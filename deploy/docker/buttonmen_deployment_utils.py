import base64
import json
import os
import re
import subprocess

BUTTONMEN_ECS_CONFIG_FILE = f"{os.environ['HOME']}/.aws/buttonmen_ecs_config.json"

SANDBOX_FQDN = 'sandbox.buttonweavers.com'
REPO_MATCH = re.compile('^origin\\s+(git@github.com:|https://github.com/)([\\w-]+)/buttonmen.git \\(fetch\\)$')

def get_subprocess_output(cmdargs):
  return subprocess.check_output(cmdargs).decode()


def buttonmen_site_fqdn(git_info):
  if git_info['site_type'] in ['replay', 'local']:
    return SANDBOX_FQDN
  if git_info['config']['use_elastic_ip']:
    return git_info['config']['bmsite_fqdn']
  return f"{git_info['branch']}.{git_info['reponame']}.{git_info['config']['bmsite_fqdn_suffix']}".replace('_', '-')

def get_database_fqdn(git_info):
  if git_info['config']['use_remote_database']:
    return git_info['config']['remote_database_fqdn']
  return '127.0.0.1'

def get_remote_database_password(git_info):
  if git_info['config']['use_remote_database']:
    return git_info['config']['remote_database_admin_pw']
  return None

def get_email_relay_host(git_info):
  if git_info['config'].get('email_relay_host', None):
    return git_info['config']['email_relay_host']
  return 'NORELAY'

def get_email_relay_sasl_creds(git_info):
  if git_info['config'].get('email_relay_sasl_creds', None):
    return git_info['config']['email_relay_sasl_creds']
  return 'NOCREDS'


# docker repos must have lowercase names
def docker_reponame(git_info):
  if git_info['site_type'] in ['replay', 'local']: prefix = git_info['site_type']
  elif git_info['image_type'] in ['stats']:        prefix = git_info['image_type']
  else:                                            prefix = 'buttonmen'
  return f"{prefix}-{git_info['reponame']}/{git_info['branch']}".lower()

def docker_shorttag(git_info):
  return f"{git_info['commitid']}"

def docker_tag(git_info):
  reponame = docker_reponame(git_info)
  shorttag = docker_shorttag(git_info)
  return f"{reponame}:{shorttag}"

def find_docker_image_with_tag(tag):
  return get_subprocess_output(['docker', 'images', tag, '--format', '{{.ID}}']).strip()


def get_working_directory_info(image_type):
  assert image_type in ["site", "stats"]
  git_info = {
    'image_type': image_type,
    'reponame': None,
    'branch': None,
    'commitid': None,
    'is_clean': True,
  }

  # Find repo name using git remote
  output = get_subprocess_output(['git', 'remote', '-v'])
  for line in output.split('\n'):
    match = REPO_MATCH.match(line)
    if not match: continue
    git_info['reponame'] = match.group(2)
  if git_info['reponame']:
    print(f"Detected buttonmen repo: {git_info['reponame']}")
  else:
    raise ValueError(f"Could not detect repo name from git remote: {output}")

  # Find branch name using git branch
  output = get_subprocess_output(['git', 'branch'])
  for line in output.split('\n'):
    if not line.startswith('* '): continue
    words = line.split()
    assert len(words) == 2, f"Found unexpected output line {line} in git branch output: {output}"
    git_info['branch'] = words[1]
  if git_info['branch']:
    print(f"Detected git branch: {git_info['branch']}")
  else:
    raise ValueError(f"Could not detect branch name from git branch: {output}")

  # Find commit ID using git show
  output = get_subprocess_output(['git', 'show', '--oneline'])
  git_info['commitid'] = output.split('\n')[0].split()[0]
  if len(git_info['commitid']) == 8:
    print(f"Detected git short commit ID: {git_info['commitid']}")
  else:
    raise ValueError(f"Expected git show --oneline output to start with an 8-character identifier, but got {git_info['commitid']}: {output}")

  # Determine whether the working directory is clean
  output = get_subprocess_output(['git', 'status', '-s']).strip()
  if len(output) > 0:
    print(f"Working directory is not clean.  Found:\n {output}")
    git_info['is_clean'] = False

  return git_info

def add_ecs_config(git_info, args):
  # Don't require or use an ECS config file for local testing
  if args['deploy_local_site']:
    file_config = {}
  else:
    if not os.path.exists(BUTTONMEN_ECS_CONFIG_FILE):
      raise ValueError(f"ECS config file {BUTTONMEN_ECS_CONFIG_FILE} does not exist - make a copy of deploy/docker/buttonmen_ecs_config.json and populate it")
    file_config = json.load(open(BUTTONMEN_ECS_CONFIG_FILE))
  if git_info['reponame'] == 'buttonmen-dev':
    key = git_info['branch']
  elif args['deploy_replay_site']:
    key = 'replay'
  elif args['deploy_local_site']:
    key = 'local'
  else:
    key = 'development'
  git_info['site_type'] = key
  git_info['config'] = file_config.get(key, {})
  if git_info['site_type'] != 'local':
    if not git_info['config'].get('network_subnet', '').startswith('subnet-'):
      raise ValueError(f"ECS config file {BUTTONMEN_ECS_CONFIG_FILE} is missing a valid 'network_subnet' entry for key {key}")
    if not git_info['config'].get('network_security_group', '').startswith('sg-'):
      raise ValueError(f"ECS config file {BUTTONMEN_ECS_CONFIG_FILE} is missing a valid 'network_security_group' entry for key {key}")
    if not git_info['config'].get('log_group', None):
      raise ValueError(f"ECS config file {BUTTONMEN_ECS_CONFIG_FILE} is missing a valid 'log_group' entry for key {key}")
    if not git_info['config'].get('filesystem_id', None):
      raise ValueError(f"ECS config file {BUTTONMEN_ECS_CONFIG_FILE} is missing a valid 'filesystem_id' entry for key {key}")

  # Non-dev branches always use a remote database; dev branches do if it's requested as a CLI
  git_info['config']['use_remote_database'] = args['use_remote_database_for_dev'] or key not in ['development', 'replay', 'local']

  if git_info['config']['use_remote_database']:
    if not git_info['config']['remote_database_fqdn']:
      raise ValueError(f"ECS config file {BUTTONMEN_ECS_CONFIG_FILE} is missing a valid 'remote_database_fqdn' entry for key {key}")
    if not git_info['config']['remote_database_admin_pw']:
      raise ValueError(f"ECS config file {BUTTONMEN_ECS_CONFIG_FILE} is missing a valid 'remote_database_admin_pw' entry for key {key}")
  elif key == 'development':
    # bzipped SQL is the file format output by buttonmen database backups, and is the only allowable input for local database loads
    if not git_info['config'].get('load_database_path', '').endswith('.sql.bz2'):
      raise ValueError(f"ECS config file {BUTTONMEN_ECS_CONFIG_FILE} is missing a valid 'load_database_path' entry for key {key}")

  # Non-dev branches always use an elastic IP; dev branches do if it's requested as a CLI
  git_info['config']['use_elastic_ip'] = args['use_elastic_ip_for_dev'] or key not in ['development', 'replay', 'local']

  if git_info['config']['use_elastic_ip']:
    if not git_info['config'].get('bmsite_fqdn', ''):
      raise ValueError(f"ECS config file {BUTTONMEN_ECS_CONFIG_FILE} is missing a valid 'bmsite_fqdn' entry for key {key}")
    if not git_info['config'].get('nlb_arn_port_80', ''):
      raise ValueError(f"ECS config file {BUTTONMEN_ECS_CONFIG_FILE} is missing a valid 'nlb_arn_port_80' entry for key {key}")
    if not git_info['config'].get('nlb_arn_port_443', ''):
      raise ValueError(f"ECS config file {BUTTONMEN_ECS_CONFIG_FILE} is missing a valid 'nlb_arn_port_443' entry for key {key}")
  elif key == 'development':
    if not git_info['config'].get('bmsite_fqdn_suffix', ''):
      raise ValueError(f"ECS config file {BUTTONMEN_ECS_CONFIG_FILE} is missing a valid 'bmsite_fqdn_suffix' entry for key {key}")

def connect_boto_clients(git_info, services):
  clients = {}
  if git_info['site_type'] == 'local':
    return clients

  import boto3
  for service in services:
    clients[service] = boto3.client(service)
  return clients


def customize_vagrant(git_info, use_email_config=True):
  bmsite_fqdn = buttonmen_site_fqdn(git_info)
  database_fqdn = get_database_fqdn(git_info)
  remote_database_password = get_remote_database_password(git_info)
  email_relay_host = get_email_relay_host(git_info)
  email_relay_sasl_creds = get_email_relay_sasl_creds(git_info)
  site_type = (git_info['site_type'] in ['replay', 'local']) and 'development' or git_info['site_type']

  cmdargs = ['cp', f'./deploy/vagrant/manifests/{git_info["image_type"]}_init.pp', './deploy/vagrant/manifests/init.pp']
  print(f"About to copy source manifest into place: {cmdargs}")
  retcode = subprocess.call(cmdargs)
  if retcode != 0:
    raise ValueError(f"Replacement failed: {retcode}")

  cmdargs = ['sed', '-i', '-e', f"s/REPLACE_WITH_DATABASE_FQDN/{database_fqdn}/", './deploy/vagrant/manifests/init.pp']
  print(f"About to install {database_fqdn} as vagrant database_fqdn: {cmdargs}")
  retcode = subprocess.call(cmdargs)
  if retcode != 0:
    raise ValueError(f"Replacement failed: {retcode}")

  cmdargs = ['sed', '-i', '-e', f"s/REPLACE_WITH_PUPPET_HOSTNAME/{bmsite_fqdn}/", './deploy/vagrant/manifests/init.pp']
  print(f"About to install {bmsite_fqdn} as vagrant puppet_hostname: {cmdargs}")
  retcode = subprocess.call(cmdargs)
  if retcode != 0:
    raise ValueError(f"Replacement failed: {retcode}")

  cmdargs = ['sed', '-i', '-e', f"s/REPLACE_WITH_BUTTONMEN_SITE_TYPE/{site_type}/", './deploy/vagrant/manifests/init.pp']
  print(f"About to install {site_type} as vagrant buttonmen_site_type: {cmdargs}")
  retcode = subprocess.call(cmdargs)
  if retcode != 0:
    raise ValueError(f"Replacement failed: {retcode}")

  if use_email_config:
    cmdargs = ['sed', '-i', '-e', f"s/REPLACE_WITH_EMAIL_RELAY_HOST/{email_relay_host}/", './deploy/vagrant/manifests/init.pp']
    print(f"About to install vagrant email_relay_host...")
    retcode = subprocess.call(cmdargs)
    if retcode != 0:
      raise ValueError(f"Replacement failed: {retcode}")

    cmdargs = ['sed', '-i', '-e', f"s/REPLACE_WITH_EMAIL_RELAY_SASL_CREDS/{email_relay_sasl_creds}/", './deploy/vagrant/manifests/init.pp']
    print(f"About to install vagrant email_relay_sasl_creds...")
    retcode = subprocess.call(cmdargs)
    if retcode != 0:
      raise ValueError(f"Replacement failed: {retcode}")

  if remote_database_password:
    cmdargs = ['sed', '-i', '-e', f"s/REPLACE_WITH_REMOTE_DATABASE_PASSWORD/{remote_database_password}/", './deploy/vagrant/manifests/init.pp']
    print(f"About to install vagrant remote_database_password...")
    retcode = subprocess.call(cmdargs)
    if retcode != 0:
      raise ValueError(f"Replacement failed: {retcode}")


def cleanup_working_directory():
  for scratch_file in [
    'Dockerfile',
    './deploy/vagrant/manifests/init.pp',
    './deploy/vagrant/manifests/init.pp-e',
  ]:
    if os.path.isfile(scratch_file):
      print(f"Cleaning up scratch file created during execution: {scratch_file}")
      os.remove(scratch_file)

def build_docker_image(git_info):
  tag = docker_tag(git_info)

  # Check if an image has already been created for this version
  image_id = find_docker_image_with_tag(tag)
  if image_id:
    print(f"Local docker image with tag {tag} already exists: ID is {image_id}")  
    return image_id

  # Copy the Dockerfile into the parent directory for the build
  cmdargs = ['cp', f'./deploy/docker/Dockerfile.{git_info["image_type"]}', './Dockerfile']
  print(f"About to cp Dockerfile into place: {cmdargs}")
  retcode = subprocess.call(cmdargs)
  if retcode != 0:
    raise ValueError(f"Failed to copy Dockerfile: {retcode}")

  # Actually build the image
  cmdargs = ['docker', 'build', '--progress', 'plain', '-t', tag, '.']
  print(f"About to build docker image locally: {cmdargs}")
  retcode = subprocess.call(cmdargs)
  if retcode != 0:
    raise ValueError(f"Image build failed: {retcode}")

  image_id = find_docker_image_with_tag(tag)
  if image_id:
    print(f"Local docker image with tag {tag} created successfully: ID is {image_id}")  
    return image_id
  raise ValueError(f"Image build reported success, but no image with tag {tag} was created")

def update_ecr_repo(reponame, ecr_client):
  try:
    repo_status = ecr_client.describe_repositories(repositoryNames=[reponame])
  except ecr_client.exceptions.RepositoryNotFoundException:
    print(f"Repository {reponame} doesn't exist - we need to create it")
    response = ecr_client.create_repository(repositoryName=reponame)
    repo_status = ecr_client.describe_repositories(repositoryNames=[reponame])
  assert len(repo_status['repositories']) == 1, f"Found unexpected number of repositories in status, expected 1: {repo_status}"
  repo_uri = repo_status['repositories'][0]['repositoryUri']
  print(f"Found repository {reponame} with URI {repo_uri}")
  return repo_uri

def find_ecr_image_with_tag(reponame, shorttag, ecr_client):
  try:
    image_status = ecr_client.describe_images(repositoryName=reponame, imageIds=[{'imageTag': shorttag}])
    print(f"Found ECR image, status is: {image_status['imageDetails']}")
    return True
  except ecr_client.exceptions.ImageNotFoundException:
    print(f"Image with tag {shorttag} not found in repo {reponame}")
    return None

def docker_login_to_ecr(ecr_client):
  response = ecr_client.get_authorization_token()
  username, token = base64.b64decode(response['authorizationData'][0]['authorizationToken']).decode().split(':', 1)
  proxy_endpoint = response['authorizationData'][0]['proxyEndpoint']
  retcode = os.system(f"echo '{token}' | docker login --username {username} --password-stdin {proxy_endpoint}")
  if retcode == 0:
    print(f"Docker login to ECR endpoint {proxy_endpoint} succeeded")
    return
  raise ValueError(f"Docker login to ECR endpoint {proxy_endpoint} failed with exit code {retcode}")

def tag_and_push_docker_image_to_ecr(image_id, repo_uri, shorttag):
  repotag = f"{repo_uri}:{shorttag}"
  cmdargs = ['docker', 'tag', image_id, repotag]
  print(f"About to tag docker image for ECR: {cmdargs}")
  retcode = subprocess.call(cmdargs)
  if retcode != 0:
    raise ValueError(f"Image tag failed: {retcode}")

  cmdargs = ['docker', 'push', repotag]
  print(f"About to push docker image to ECR: {cmdargs}")
  retcode = subprocess.call(cmdargs)
  if retcode != 0:
    raise ValueError(f"Image push failed: {retcode}")

def push_docker_image_to_ecr(git_info, image_id, ecr_client):
  reponame = docker_reponame(git_info)
  shorttag = docker_shorttag(git_info)
  repo_uri = update_ecr_repo(reponame, ecr_client)
  ecr_image_id = find_ecr_image_with_tag(reponame, shorttag, ecr_client)
  if not ecr_image_id:
    docker_login_to_ecr(ecr_client)
    tag_and_push_docker_image_to_ecr(image_id, repo_uri, shorttag)
  return repo_uri

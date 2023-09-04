FROM --platform=linux/amd64 ubuntu:16.04
RUN mkdir /buttonmen
WORKDIR /buttonmen
COPY . .

# Make sure log dir exists
RUN mkdir -p build/logs

# Bootstrap puppet
RUN bash ./deploy/vagrant/bootstrap.sh

# Run puppet
RUN puppet apply --modulepath=/buttonmen/deploy/vagrant/modules /buttonmen/deploy/vagrant/manifests/init.pp

CMD ["/bin/bash", "/buttonmen/deploy/docker/startup.sh"]

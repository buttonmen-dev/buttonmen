FROM --platform=linux/amd64 ubuntu:24.04
RUN mkdir /buttonmen
WORKDIR /buttonmen
COPY . .

# Make sure log dir exists
RUN mkdir -p build/logs

# Bootstrap puppet
RUN bash ./deploy/vagrant/bootstrap.sh

# Run puppet
RUN bash ./deploy/vagrant/run_puppet.sh

# Run startup script
CMD ["/bin/bash", "/buttonmen/deploy/docker/startup.sh"]

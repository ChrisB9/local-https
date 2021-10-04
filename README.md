# LOCAL real HTTPS. With the help of acme.sh dnsapi

You want a local Https certificate that is real? Accepted by every Browser?
With your domain and a little help of Docker you can do it.

````yml
version: '3.5'
services:

  proxy:
    restart: unless-stopped
    image: nginxproxy/nginx-proxy
    ports:
      - "80:80"
      - "443:443"
    volumes:
      - /var/run/docker.sock:/tmp/docker.sock:ro
      - ./.docker/data/nginx/certs:/etc/nginx/certs
      - ./.docker/data/nginx/dhparam:/etc/nginx/dhparam
    labels:
      - com.github.chrisb9.local_https.nginx_proxy

  companion:
    restart: unless-stopped
    image: chrisb9/local-https
    volumes:
      - /var/run/docker.sock:/var/run/docker.sock:ro
      - ./.docker/data/letsencrypt:/etc/letsencrypt
      - ./.docker/data/nginx/certs:/etc/nginx/certs
    env_file:
      - .env
    environment:
      - DNS_CLIENT=${DNS_CLIENT:?must be set}
      - HTTPS_MAIN_DOMAIN=${HTTPS_MAIN_DOMAIN:?must be set}
      - CUSTOM_LABEL=com.github.chrisb9.local_https.nginx_proxy
````

Create a `.env` file:
````.env
# required:
DNS_CLIENT=cf
HTTPS_MAIN_DOMAIN=your-domain.com

# see https://github.com/acmesh-official/acme.sh/wiki/dnsapi for the correct key you want to use
CF_Email=
CF_Key=

# optional:
MATTERMOST_TOKEN=
MATTERMOST_URL=
NOTIFICATION_TYPE=mattermost # or slack if you want to use slack
````

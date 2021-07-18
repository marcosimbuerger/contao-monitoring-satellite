# Monitoring Satellite for Contao 🛰

The Monitoring Satellite provides data about your Contao CMS for the Monitoring Station.

### Installation
```bash
$ composer require marcosimbuerger/contao-monitoring-satellite
```

## Configuration
Add the basic authentication credentials to the parameters.yml file.

Use `vendor/bin/contao-console security:encode-password` to generate the password hash.

Encode your password with the `Symfony\Component\Security\Core\User\User` encoder.

```yaml
# app/config/parameters.yml

parameters:
    ...
    monitoring_satellite:
        basic_auth:
            username: foo
            password: '$argon2id$v=19$m=65536,t=4,p=1$ofPY6RT+0rCE74M0AlPpzQ$BeiGUhv27D4/6FBmNKC0r4dhImZqj55EfOwYqjxaVbE'
```

## Test
Call `/monitoring-satellite/v1/get`.

It should be protected by basic authentication and return the data after successful authentication.

## Add the Satellite to the Station
Add this Monitoring Satellite to the Monitoring Station. See [documentation of the Monitoring Station](https://github.com/marcosimbuerger/monitoring-station).

## License
This bundle is released under the MIT license. See the included [LICENSE](LICENSE) file for more information.
# Configure Keycloak authentication

## Security
Configure your firewall to use the 'keycloak' authenticator (and allow anonymous).
Set the logout path and provide the 'success_handler' as shown below.
```yaml
    firewalls:
        main:
            anonymous: ~
            keycloak: true
            ...
            logout:
                path: /oidc/logout
                success_handler: emsa.firewall.logouthandler.keycloak
            ...
```
Make sure all authentication paths are accessible by anonymous users in your access_control definitions:
```yaml
    access_control:
        - { path: ^/logout$, role: IS_AUTHENTICATED_ANONYMOUSLY }
        - { path: ^/oidc/, role: IS_AUTHENTICATED_ANONYMOUSLY }
```

## EMSAuthentication
The following configuration options are to be defined in the 'ems_authentication' key:
```yaml
ems_authentication:
  redirect_url: 'https://your.domain/after/login/url'
  post_logout_redirect_url: 'https://your.domain/logout'
  client_id: 'your_keycloak_id'
  client_secret: 'YourSecretForKeycloak'
  authorize_url: 'https://keycloak.server/auth'
  realm: ~
```

## Routing
Add the routes for keycloak login/logout to routes.yaml:
```yaml
ems_authentication:
    resource: '@EMSAuthenticationBundle/Resources/config/routing/keycloak.xml'
```

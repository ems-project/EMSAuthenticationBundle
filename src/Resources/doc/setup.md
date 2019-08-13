Configure your security settings
---
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
                success_handler: EMS\AuthenticationBundle\Firewall\OpenIdConnectLogoutSuccessHandler
            ...
```
Make sure all authentication paths are accessible by anonymous users in your access_control definitions:
```yaml
    access_control:
        - { path: ^/logout$, role: IS_AUTHENTICATED_ANONYMOUSLY }
        - { path: ^/oidc/, role: IS_AUTHENTICATED_ANONYMOUSLY }
```

The following configuration options are to be defined in the 'idp' key:
```yaml
ems_authentication:
  redirect_url: 
  post_logout_redirect_url: 
  client_id: 
  client_secret: 
  authorize_url: 
  realm: 
```

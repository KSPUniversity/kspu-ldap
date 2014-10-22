<?php

namespace Kspu\LDAP\Provider;

use Silex\Application;
use Silex\ServiceProviderInterface;

use FR3D\LdapBundle\Security\User\LdapUserProvider;
use FR3D\LdapBundle\Ldap\LdapManager;
use FR3D\LdapBundle\Driver\ZendLdapDriver;
use FR3D\LdapBundle\Security\Authentication\LdapAuthenticationProvider;

use Zend\Ldap\Ldap as ZendLdap;

use Kspu\LDAP\Entity\UserListener;

class LDAPServiceProvider implements ServiceProviderInterface {
    public function register(Application $app) {
        $type = 'ldap';
        $lType = 'form';

        /** @noinspection PhpParamsInspection */
        $app['security.authentication_listener.factory.'.$type] = $app->protect(function($name, $options) use ($type, $app, $lType) {
            $provider = 'ldap';

            if (!isset($app['security.authentication_listener.'.$name.'.'.$lType])) {
                $app['security.authentication_listener.'.$name.'.'.$lType] = $app['security.authentication_listener.'.$lType.'._proto']($name, $options);
            }

            if (!isset($app['security.authentication_provider.'.$name.'.'.$provider])) {
                $app['security.authentication_provider.'.$name.'.'.$provider] = $app['security.authentication_provider.'.$provider.'._proto']($name);
            }

            return array(
                'security.authentication_provider.'.$name.'.'.$provider,
                'security.authentication_listener.'.$name.'.'.$lType,
                null,
                $lType
            );
        });

       /** @noinspection PhpParamsInspection */
        $app['security.authentication_provider.ldap._proto'] = $app->protect(function ($name) use ($app) {
            /** @noinspection PhpParamsInspection */
            return $app->share(function () use ($app, $name) {
                return new LdapAuthenticationProvider(
                    $app['security.user_checker'],
                    $name,
                    $app['security.user_provider.' . $name],
                    $app['security.ldap.ldap_manager'],
                    $app['security.hide_user_not_found']
                );
            });
        });

        /** @noinspection PhpParamsInspection */
        $app['security.ldap.user_provider'] = $app->share(function() use($app) {
            return new LdapUserProvider(
                $app['security.ldap.ldap_manager'],
                $app['security.ldap.logger']('security')
            );
        });

        /** @noinspection PhpParamsInspection */
        $app['security.ldap.ldap_manager'] = $app->share(function() use($app) {
            $parameters = array('filter' => '', 'baseDn' => '', 'attributes' => '');

            $userParameters = $app['security.ldap.ldap_manager.parameters'];
            $mapping = $userParameters['mapping'];
            unset($userParameters['mapping']);

            $attributes = array();
            foreach($mapping as $ldap_attr => $user_method) {
                $attributes[] = array('ldap_attr' => $ldap_attr, 'user_method' => $user_method);
            }

            $userParameters['attributes'] = $attributes;

            $parameters = array_merge($parameters, $userParameters);

            return new LdapManager(
                $app['security.ldap.ldap_driver.zend'],
                $app['security.ldap.user_manager'],
                $parameters
            );
        });

        /** @noinspection PhpParamsInspection */
        $app['security.ldap.ldap_driver.zend'] = $app->share(function() use($app) {
            return new ZendLdapDriver(
                $app['security.ldap_driver.zend.driver'],
                $app['security.ldap.logger']('ldap_driver')
            );
        });

        /** @noinspection PhpParamsInspection */
        $app['security.ldap_driver.zend.driver'] = $app->share(function() use($app) {
            return new ZendLdap(
                $app['security.ldap_driver.options']
            );
        });

        /** @noinspection PhpParamsInspection */
        $app['security.ldap.logger'] = $app->protect(function($name) use($app) {
            return isset($app['monolog.logger.class'])
                ? new $app['monolog.logger.class']($name)
                : null;
        });

        /** @noinspection PhpParamsInspection */
        $app['security.ldap.user_listener'] = $app->share(function() use($app) {
            return new UserListener(
                $app['security.ldap.user_manager']
            );
        });
    }

    public function boot(Application $app) {
        /** @noinspection PhpUndefinedMethodInspection */
        $app['dispatcher']->addSubscriber($app['security.ldap.user_listener']);
    }
}
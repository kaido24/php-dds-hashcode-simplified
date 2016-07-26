hashcode-lib sample application
===============================

Installation and setup
----------------------

Make sure you have installed the Composer dependency manager: https://getcomposer.org/

Download the necessary dependencies:  

```bash
  composer install
```

Vagrant setup
-------------

Make sure You have installed following componetns
* VirtualBox [download from here](https://www.virtualbox.org/wiki/Downloads)
* Vagrant [download](https://www.vagrantup.com/downloads.html)
* Ansible [installation instructions](http://docs.ansible.com/intro_installation.html)

Project structure
-----------------

```
├── ansible # Ansible vagrant configuratin YAML
├── example
│   ├── app
│   │   ├── DigiDocService
│   │   ├── commons
│   │   ├── content # Sample application HTML templates
│   │   │   └── modals
│   │   ├── helpers
│   │   ├── template
│   │   └── upload # Place where signed documents will be uploaded (must be writable)
│   └── web # Web server public directory
│       └── assets # Static assets
│           ├── css
│           ├── fonts # Bootstrap icon fonts
│           ├── images
│           └── js # Contains all required JS libs (hwcrypto, jQuery etc)
├── hashcode-lib
│   └── src
│       └── SK
│           └── Digidoc
├── server-config # Nginx and Upstart configuration files for Vagrant Ubuntu server
│   ├── nginx
│   │   └── certs # Self signed certificates for Nginx
│   └── upstart-onfig # Sample PHP application Upstart configuration
└── vendor # Composer packages and autoloader
    └── composer
```

Running sample application
--------------------------

There are two options to run applications out of the box:
* Using built-in PHP server (only works to test mobile signing) DigiDoc utility requires SSL
* Using Vagrant. Provided Vagrant configuration provides all the required components (nginx, PHP, self signed certs)

Running using Built-in PHP Server:

``` bash
php -S localhost:8000 -t /full/path/to/dds-hashcode/example/web
```
Running sample application using Vagrant:

``` bash
vagrant up 
```

After all the provisioning has been completed You can navigate to [10.33.33.33](http://10.33.33.33)
and all should work.

Running unit tests
------------------

Run PHPUnit (under Windows, use "phpunit.bat"). Run command inside dds-hashcode:  

```bash
  ./vendor/bin/phpunit --configuration hashcode-lib/tests/phpunit.xml
 ```  
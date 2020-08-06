# Tero Install
Command Line to install tero with halcon ready to develop

### How works
Position the root of the webserver where you want to install the project on your console. The Script will ask you for a folder name where to install the files. Make sure you have the necessary permissions to run the command.

-Require PHP and GIT installed
- b64: https://raw.githubusercontent.com/dromero86/tero-install/master/tero_install.b64

# Windows
```
PS> php -r "eval(base64_decode(file_get_contents('https://raw.githubusercontent.com/dromero86/tero-install/master/tero_install.b64')));"
```

# Linux 
```
$ php -r "eval(base64_decode(file_get_contents('https://raw.githubusercontent.com/dromero86/tero-install/master/tero_install.b64')));"
```

### Server multi projects
If your server creates multiple projects we recommend having an alias configured

Create alias in linux
```
alias tero-get="php -r \"eval(base64_decode(file_get_contents('https://raw.githubusercontent.com/dromero86/tero-install/master/tero_install.b64')));\""
```


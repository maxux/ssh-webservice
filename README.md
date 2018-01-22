# ssh-webservice
This is the source code of ssh.maxux.net

# Service
This service allows you to add a github user ssh key to a host in a single bash line:
```
curl https://ssh.maxux.net/[username] | bash
```

By default, the first key is used (see github.com/`username`.keys).

You can choose another line explicitly like (eg: to use line 3):
```
curl https://ssh.maxux.net/[username]/3 | bash
```

Note: this script doesn't allow a key that is already authorized

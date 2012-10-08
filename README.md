ipython-hydra
=============

Set of scripts to automatically launch iptyhon notebooks for each user who hits the page.

To set up ipython.stanford.edu, we did a standard linux-apache web server install and installed the latest ipython (with easy install). We also also installed some other python packages that we wanted, like the nipy suite.

Additional configuration:

    sudo su
    addgroup --system ipython
    echo “www-data ALL=(%ipython) NOPASSWD: /usr/local/bin/ipynb-launch” >> /etc/sudoers.d/ipython
    echo “www-data ALL=(root) NOPASSWD: /usr/local/bin/addldapuser” >> /etc/sudoers.d/ipython
    mkdir /var/log/ipython
    chown www-data.ipython /var/log/ipython
    chmod g+w /var/log/ipython

The php scipt calls the two bash scripts, where all the hard work happens:
 * addldapuser will add a new user to the system, if they don't already exist. It must be run as root. The new user will have their password disabled, so by default they can't log in. They will also be added to the ipython group. If the user exists in Stanford LDAP, then their UID will be assigned by the LDAP result. Otherwise, a local UID is used. 
 * ipynb-launch will configure a new user's home directory (if it hasn't already been done) by setting up the ipython config file and checking out the tutorial notebooks from github. If the notebooks already exist, git pull is run to get any new items. However, we ensure that any existing notebooks are kept as the user last left them to ensure that we don't corrupt any of their edits. (NOTE: this part of the code suck particularly badly! Please tell me how to do this elegantly with git!)



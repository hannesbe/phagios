Phagios
======

Phagios is PHP helper library for building Nagios plugins in PHP.
It is work-in-progress alpha and is not recommended for use in production environments.

Requirements
------------

- PHP 5
- Nagios 3+

Installation and Usage
------------

Installing Phagios can be done, first by cloning this repository.
```  
git clone git://github.com/patyx7/phagios.git <directory>
```
Then including Phagios at the top of your PHP plugin file, having your plugin class extend Phagios, and making sure to run Phagios's main method, which will in turn run your plugin class' run method.

```  
<?php

require './<directory>/Phagios.php';

class yourNagiosPlugin extends Phagios 
{
    protected function run()
    {
        // Your plugin code here....
    }
}
```

Contribute and Feedback
------------

Please submit issues and send your feedback and suggestions as often as you have them.
Also feel free to fork the project, create a feature branch, and send me a pull request and I will review it.

License
-------

Phagios is licensed under the GNU GENERAL PUBLIC LICENSE - see the [LICENSE.md](LICENSE.md) file for more details.

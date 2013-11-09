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

For now installing can be done by cloning the repository
```  
git clone git://github.com/patyx7/phagios.git <directory>
```
and then including helper library at the top of your PHP plugin file.
```  
$phagios = require './<directory>/Phagios.php';
```

Contribute and Feedback
------------

Please submit issues and send your feedback and suggestions as often as you have them.
Also feel free to fork the project, create a feature branch, and send me a pull request and I will review it.

License
-------

Phagios is licensed under the GNU GENERAL PUBLIC LICENSE - see the [LICENSE.md](LICENSE.md) file for more details.

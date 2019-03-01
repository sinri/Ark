# Welcome to Ark II

[![GitHub license](https://img.shields.io/badge/license-MIT-blue.svg)](https://raw.githubusercontent.com/sinri/Ark/master/LICENSE) 
[![GitHub release](https://img.shields.io/github/release/sinri/Ark.svg)](https://github.com/sinri/Ark/releases)
[![Packagist](https://img.shields.io/packagist/v/sinri/ark.svg)](https://packagist.org/packages/sinri/ark) 

Fork: https://github.com/sinri/Ark

```bash
composer require sinri/ark
```

It is a new generation for Enoch Project, as which might continuously support projects in PHP 5.4+.

> And every living substance was destroyed which was upon the face of the ground, both man, and cattle, and the creeping things, and the fowl of the heaven; and they were destroyed from the earth: and Noah only remained [alive], and they that [were] with him in the ark. (Genesis 7:23)

## What is Ark? 

Ark is originally a boat-like container created by Noah, 
to make creatures inside survive during the great flood, 
according to the Holy Bible. 

Here, Ark is an universal framework for PHP 7, 
supporting serving web sites, RESTful API services, or even CLI programs,
with increasing components for various usages, such as log, cache, database, email, even PHP language tricks.

## Why design Ark?

It is obviously that much more users using Laravel, Lumen, CodeIgniter, etc.
I am also a user of those frameworks, even now with the existed projects of my company.
But sometimes, I would feel the job of coding is out of my control.
It is a terrific experience when I face some strange issues, 
which might spend me a lot of time to read the code and run test, and try on various environments before fixing them.
The documents of those frameworks, sometimes work, sometimes not and even just a lie. 
When the documents were sick, reading source code would be the only choice to understanding the reason of problems.
But the source code is commonly too unfriendly for quick reference.

One thing more, the frameworks always contain their own components or design rule in user's own project, 
rather than dwell inside vendor, as I usually want, that the framework to be a completely third party library.

So as my will, I already wrote [Framework Enoch](https://github.com/sinri/enoch), which supports PHP 5.4 or later.
But it is not a clean framework as I write it firstly for the project of my company for doing CLI tasks,
though it is now serving almost all PHP service of my own and my company.
With Debian 8 and newer version, PHP 7 became the default version, 
I planned a new framework for PHP 7 with better design, yes that is Framework Ark.  

## What can Ark help?

The principle of Framework Ark, is simple, "Help Yourself".
In the days of Noah, Ark was built and open awaiting, but none survived in the great flood but the family of Noah,
and all those livings inside the Ark.
Ark would never force you do anything, you just take this project as an third party library.

Ark provides a lot of toolkit to support your single task. 
Ark can help you do class auto loading following PSR-4 standard.
Do you need Array Operation, Database IO, Cache Management, Logging Delegate, etc? 
All those requirements are supported by toolkit of Ark.

If you need to serve a web site, or provide an HTTP RESTful API service, 
Ark can do this just like Lumen and CodeIgniter.

If you need to carry out a CLI program, Ark provides you a solution to handle the arguments.


 
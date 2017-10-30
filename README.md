# Variable-Solutions #

The Variable-Solutions plugin adds global variables to the WordPress content management system.

## Description ##

The Variable-Solutions plugin extends WordPress enabling the creation and management of variables that can be referenced as short codes in WordPress content and/or as defined variables in the supporting PHP files. It is a thin plugin and loads only one file when running in end user mode. The Variable-Solutions plugin is developed and maintained by <a href="https://www.usi2solve.com">Universal Solutions</a>.

**What are Variables** - Say you have some information that changes frequently and appears on multiple pages.
It could be the date of your next big event or even the product of the month. 
Keeping track of where this information appears on your site and making consistent updates can be a labor intensive and error prone process.
The Variable-Solutions plugin uses shortcodes to give each piece of information a unique "*variable*" name. 
These unique variable names, along with the information the name represents, are easily managed in the admin back end.

**What are Shortcodes** - Shortcodes are special tags used as shortcuts to easily insert bits of functionality into your content. 
You can recognize shortcodes by square brackets that surround a simple word or a phrase.

**Show Me an Example** - If your site features different product of the month, you can create a variable ` 'product_month' ` and set it to ` "Our Nifty Widget" `, 
then you can include this variable anywhere in your content with the following shortcode:

```
[variable item="product_month"]
```

and your visitors will see ` "Our Nifty Widget" ` wherever the above shortcode is used. 
Next month when your product changes, you can edit the ` 'product_month' ` variable and set it to ` "Our More Niftier Widget" ` 
and your visitors will see the updated content.

## Installation ##
The Variable-Solutions plugin follows the standard WordPress <a href="https://codex.wordpress.org/Managing_Plugins#Manual_Plugin_Installation">manual plugin installation</a> procedure:
1. Download the Variable-Solutions archive to your computer.
1. Extract the archive contents to your local file system.
1. Rename the extracted folder to ` usi-variable-solutions ` if not already done so during the extraction.
1. Upload the ` usi-variable-solutions ` folder to the ` wp-content/plugins ` folder in your target WordPress installation.
1. Activate the plugin via the WordPress *Plugins* menu located on the left side bar.

## Implementation ##
The Variable-Solutions plugin stores the variables you create in the WordPress database and writes them to the *variables.php* file. 
This file contains PHP define statements that can be referenced by code in your system.
Using the example from above, the *variables.php* file would contain the following line:

```php
define('prefix_PRODUCT_MONTH', 'Our Nifty Widget');
```
Any custom theme or plugin code in your WordPress installation can reference the `prefix_PRODUCT_MONTH ` variable and get the value `'Our Nifty Widget'`.

You can control the location of the *variables.php* file and other configuration options via the *Variable-Solutions* page under the WordPress *Settings* 
menu located on the left side bar.

## Settings ##
The Variable-Solutions settings page contains three tabs: Preferences, Publish and Capabilities.

**Preferences** - allows you to change the default options set during plugin installation.

**Publish** - any changes you make to you variables must be published before the changes appear to the world.

**Capabilities** - allows you to set the role capabilites system-wide or for a specific user on a user-by-user basis. 

## Usage ##
Click on the WordPress *Variables* menu located on the left side bar to see the list of variables that are in your WordPress installation.
Click the ` Add New ` button to add a new variable or hover over a variable name and click the ` Edit ` link to edit an existing variable.

The edit variable page shows the variable category and variable name for the selected variable along with some other parameters. 
The Variable-Solutions plugin comes with the built-in ` general ` category which is used by the shortcode if no category is given. 
There is no harm in specifying the ` General ` category, in which case the shortcode would be written as follows:

```
[variable category="general" item="product_month"]
```

**Note** - If you edit an existing variable and change it's category or variable name, then the old category and variable name pair will be repalced with the new combination.
This can cause referencing errors if you don't make the corresponding changes in your content.

Additionally, there is the built-in ` date ` category for inserting dates into your content. 
Enter the date using the 24-hour clock, year first then time format as follows:

```
1970-01-01 00:00:00
```
You can then specify how the date will be displayed when WordPress processes the shortcode. For example:

```
[variable category="date" item="unix_epoch" format="F js, Y"]
```

will display ` January 1st, 1970 `, which is the format specified by the ` F js, Y ` string. 
You can find the format string parameter reference here: <a href="http://php.net/manual/en/function.date.php">php.net/manual/en/function.date.php</a>.

## Screen Images ##

![Variable List Page](https://user-images.githubusercontent.com/16763256/32197597-40eb1c98-bd9b-11e7-938e-cf46aefd9973.png "Variable Llist Page")
Screen Image 1 - Variable List Page

<br>

![Add Variable Page](https://user-images.githubusercontent.com/16763256/32197596-40df18b2-bd9b-11e7-8477-3a460bbe7740.png "Add Variable Page")
Screen Image 2 - Add Variable Page

## License ##
> Variable-Solutions is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License 
as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.

> Variable-Solutions is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty 
of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.

> You should have received a copy of the GNU General Public License along with Variable-Solutions.  If not, see 
<http://www.gnu.org/licenses/>.

## Donations ##
Donations are accepted at <a href="https://www.usi2solve.com/donate/variable-solutions">www.usi2solve.com/donate</a>. Thank you for your support!
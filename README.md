## PHP-Fusion CMS Version 9.10.03A-DEV (Development)

This is the updated Version 9.10.03 stable that is currently being used on phpfusion.com, and will be receiving further updates for issues.

All further development progress on the V9 latest standard should push to this branch. Do NOT push to 9.10.03 

## Minimum Requirements:
Technical Requirements to start using PHPFusion v9.10.03A will be as following:

**PHP version:** 8.1.0 (Recommended 8.2)

**MYSQL version:** MYSQL 8.0.13 (Recommended MySQL 8.1)

**Others**: OpCache (strongly recommended), Redis, Memcached, GDLib, Internationalization packages should be enabled on Apache.

## Developer Welcome Guide
This guide will help you get started with PHPFusion development. It covers setting up your environment, installing necessary tools, and configuring your IDE.

If you are keen on creating new web software like the rest of the world, you should be ready to dive into PHPFusion development. This guide will help you get started with setting up your environment, installing necessary tools, and configuring your IDE.

## Getting Started with PHPFusion Development
This guide will help you get started with setting up your environment, installing necessary tools, and configuring your IDE.

### Setting up PHPFusion working environment.
This is a guide to accellerate your programming IDE setup for PHPFusion v9.10.03A. Time to update your Notepad++ to another free IDE like VS Code from Microsoft. 
The advantage of using IDE is that it provides a rich set of features like code completion, syntax highlighting, debugging tools, and more. It also allows you to write cleaner and more maintainable code.

### Install VS Code
Download and install Visual Studio Code from [here](https://code.visualstudio.com/).

### Install Node.js
-Go to the official Node.js website: https://nodejs.org/.
-Download the LTS version (recommended for most users).

### Install LESS Globally via npm
Now that you have Node.js installed, you can install LESS globally on your system
1. Open Command Prompt or PowerShell.
2. Install LESS: Run the following command to install LESS globally using npm:

```bash
npm install -g less
```
The -g flag ensures that LESS is installed globally, allowing you to use it from anywhere on your system.

### Using LESS 
Now that LESS is installed, you can start using it to compile .less files into .css files.

Compile LESS to CSS: For example, to compile a main.less file into a main.css file, use the following command in your terminal:
```bash
lessc main.less main.css
```
If you want to minify the output CSS, use the --clean-css option:
```bash
lessc --clean-css main.less main.min.css
```

### Style Editing - The easy way
After you installed less, in VSCode, Go Terminal, select Run Task. With task.json file in the root directory, you can just select and run the task accordingly. Editing .less files are very convenient as they are just like ordinary .css but with more powerful nest and function. 



PHP-Fusion Babylon
---

This is the development of PHP-Fusion Version 10.00.XX, code name Babylon.

[![Codacy Badge](https://api.codacy.com/project/badge/Grade/3702a8fcb1214628bc7c721340d775d8)](https://www.codacy.com/app/FrederickChan/PHP-Fusion?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=php-fusion/PHP-Fusion&amp;utm_campaign=Badge_Grade)

Welcome to the PHP-Fusion Official Repository
====
The latest branch is Babylon. It contains all the previous commits done by all the core developers up to date.

To sync to this database and not to conflict with your work, please do the following before doing anything:

  1. Save all your current local work to a temporary folder.
  2. Run the git as
  ````
  $ git fetch origin
  $ git checkout -b Babylon origin/Babylon
  ````
  or if above fails:
  ````
  $ git fetch origin Babylon:Babylon
  ````

#### Testing supported by

<a href="https://www.browserstack.com/" target="_blank"><img width="180px" src="https://www.php-fusion.co.uk/images/logos/Browserstack-logo.svg" alt="BrowserStack"/></a>

Development Discord
---
PHP-Fusion Official Developer Discord Channel - https://discord.gg/CGSYU8r

Those who are active on PHP-Fusion Development is encouraged to join.

Gulp.js and BrowserSync Enabled in Development
===
This technology is available to the community to be implemented to allow developers to have node.js push changes to screen without the need to refresh (F5).

**Quick Guide**
In your ***root folder***, run in terminal these command to install SASS, GULP and BrowserSync in your local development environment.
```
npm install --save-dev gulp gulp-saas browser-sync
```

Next, included in root folder is a gulpfile.js. Edit that file and change the values of just the ``proxyURL`` to match your local environment. (i.e. "localhost/babylon" )
That's it, edit any php, css, sass, twig code in your IDE or code editor, your browser will be configured to auto refresh as changes are saved.

Now, to run it, open terminal again, type in the following to start the service and a new browser will pop up to localhost:3000, as a mirror to your current site.
```
gulp watch
``` 

Less.js & Scss support
===
By default Fusion will have these declared.
- Theme CSS
- (+) Default Fusion Root CSS
- (+) Bootstrap CSS
- (+) Template CSS
- (+) @Import Google Font, etc. 
 
When you add multiple system which all have their own predefined rulesets and create dependencies on default css, things will be messy without proper control and hard to mod with. 
A new solution to handle this is to get supported by dynamic less.js lib for system css management. 

**To get started with developing your theme with .less css, you will need to install node and npm into your local system to compile .less files to .css files**

If you haven't already, install <a href="https://www.npmjs.com/get-npm">npm</a>. 
Then run the following command in terminal. (Windows Powershell for Windows or Terminal for Mac/Linux or, whatever system command tools you have)

- Install LESS.js, type in this command to :
```
npm install --save-dev less
```
- Install css minifier, type in this command:
```
npm install -save-dev less-plugin-clean-css
```

The requirements of the theme now is a less.js file, a css file and a map file for debugging purposes. 
You can copy the following code and paste it into your ``render_page()`` function.
```
add_to_footer("<script src='".INCLUDES."jquery/less.min.js'></script>");
add_to_head("<link rel='stylesheet' href='".THEME."custom.css' type='text/css' />");
add_to_head("<link rel='stylesheet' href='".THEME."custom.css.map' type='text/css' />");
```
To add extra configurability to your site, the theme **styles.css will not be required of a theme, but will be included if it exists.** This is for backwards compatibility purposes only. If you do not want styles.css, delete that file from your theme folder.

**Working and compiling with CSS Less Preprocessor**
 
Please refer to the <a href="http://lesscss.org/usage/#command-line-usage-server-side-and-command-line-usage">less.js documentation</a>.
For IntelliJ users, you can add a Filewatcher to do this automatically for you everytime it saves. <a href="https://www.jetbrains.com/help/phpstorm/transpiling-sass-less-and-scss-to-css.html">Instructions are found here</a>.

If you set it up correctly, web inspector will show you a .less source instead of a .css source. From here on, we will work on an Auto**magic** method to change css output on different templates to optimize page load times. 

New Changes: About Submodules and Git Management of Core Infusions (CI)
===
As of latest update on code management, the core team will have our <a href='https://github.com/php-fusion/PHP-Fusion/tree/Babylon/infusions'>Latest Core Infusions</a> to see them as a submodules. Submodules are alias that are being linked from each CI repositories now being for example,
ci-Forum, ci-News, ci-Blog, ci-Weblinks, ci-Faq, ci-Downloads, ci-Gallery, etc.

PHP-Fusion 9 will now release with just "Custom Page, Navigation, Users Management, SEF Management, and Server Management", essentially a bare bone system.
The core developers will in turn provide a download customizer to bundle your download package in the future.

Each CI will be enhanced through its own review and issue progress independent of PHP-Fusion CMS.

Each CI package will have better reviews to each versions instead of collectively just being tied to PHP-Fusion Version as a whole.

Independent CI repositories serves as a better code and project tracker for the better quality of Infusions, and each of their progress will not hinder PHP-Fusion releases. As a guideline to everyone, always use the 'master' branch for CI repository for they are the most stable to use.

For those who are more into experimental, feel free to checkout the latest version, but we cannot guarantee you will be in a bug free state. All issues regarding the CIs will be moved to the CI repository.

How does this affect pulling and cloning from Git as a Developer?
===
While developer will work on the next generation in the branches respectively, which often destabilize a stable package, The Main Repository Infusions page will be always tied to the 'master' branch of the CI repositories which will contain the latest stable copy.
If you are a developer and wish to test out on a specific working branch, you need to use a git checkout for submodule management.

**Cloning of the main repository including updating all CI repository submodules:**
````git
C:\ git clone --recursive https://github.com/php-fusion/PHP-Fusion.git php-fusion
````
In order to switch branch in the CIs, you need to browse to the `infusions/<name of the ci>` folder:
````git
C:\php-fusion> cd infusions/forum
````
Checkout to new branch (using the CI's own VCS root):
````git
C:\php-fusion\infusions\forum> git checkout 2.0
````
Updating (using the CI's own VCS root):
````git
C:\php-fusion\infusions\forum> git submodule update
````
Pulling a CI changes (using the CI's own VCS root):
````git
C:\php-fusion\infusions\forum> git pull
````

New Class, Method, Function Naming Convention
===
This will be one of the main things to do in the Babylon project.

Please observe the new naming convention system, as we will be refactoring the entire system files to the following:

**File Name:** file-name.php or file_name.php are accepted.

**Function Name:** some_function() {} using snake case

**Class Name:** Some_Class {} using camel case where the first letter is an Uppercase and if file is separated with a - to be represented with a _ underscore in the class name.

**Class Method Name:** public someFunction() {} using pascal case where the first letter is a Lowercase.

**Other Notes on File Naming:**

On PSR4 autoloaders issues, we'll do both - , _ and implement strict strtolower on filenames with a common goal to support all files in the PHP-Fusion project scope.
Further update will follow as we observe the growth of the project.

You can still run .inc but those will not be supported by PHP-Fusion Autoloader anymore. A custom autoloader will be needed from your end.

The file name must be in all small caps, not .inc but .php extension.

Just in case, in some rare scenarios, that if the files are already existing public url files, **please do not refactor the file name**. You must create a new file and put up a 301 on the old file and redirect it to the new one. This will prevent users losing page rank on live sites.

If you want special identifiers for a subset of class file, you can have them renamed as file-name.classname.php

**Special Note Regarding Function Naming:**

When the function/method does something, point it out as a prefix. A normal convention is to add a verb in front of the function name like 'do, get, set, define, etc'. Implementation example is such as 'doSomeFunction(), setSomeInfo(), defineConstant(), etc'.

This way it is simpler on the eyes to figure out what the function does before going through the function docs.

New Core Team Project Colloboration
====
The project colloboration is now available at https://github.com/orgs/php-fusion/projects/1
All issues are tied to the project for team colloboration, with assigned tasks based on volunteering effort or best person which has been closest tied to such issue. The Kanban cards are useful for fast turnover and clear monitoring of all task that blocks the project from being completed and we will work based on fixing that identified blockage.

All Core Developers are given Admin Access for Read and Write. So feel free to add any critical issues that is critical to address for the completion of the project.

We will be preparing Babylon for public launch as soon as the Kanban Cards are done.

New development version naming system
---
For each subsequent branch - Core Developers will vote for new branch name for the next release.
Steps will involve "Nomination". We will be following A-Z chronological method in the naming system.
The names can be picked from any names from Space elements. Nomination and polls will be voted on the official project forum.
Note that the public release may not reflect the name system in developer branch naming.

The number prefix behind each branch naming will be determined at MT's discretion on making public releases.

Infusions, Locale and Themes
---
We will be adding codes to new repositories, in which only master branch shall apply. If you need versioning to the packages here, please do it on your own personal account and submit the master and latest version to these repository.

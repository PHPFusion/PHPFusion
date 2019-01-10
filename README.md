PHP-Fusion Andromeda 
---
This is the development of PHP-Fusion Version 9.0.3, code name Andromeda.

<<<<<<< HEAD
Welcome to the PHP-Fusion Official Repository
====
The latest branch is Andromeda-9.0.3. It contains all the previous commits done by all the core developers up to date. 

To sync to this database and not to conflict with your work, please do the following before doing anything:
  
  1. Save all your current local work to a temporary folder.
  2. Run the git as 
  ````
  $ git fetch origin
  $ git checkout -b Andromeda-9.0.3 origin/Andromeda-9.0.3
  ````
  or if above fails: 
  ````
  $ git fetch origin Andromeda-9.0.3:Andromeda-9.0.3  
  ````
 
#### Testing supported by

<a href="https://www.browserstack.com/" target="_blank"><img width="180px" src="https://www.php-fusion.co.uk/images/logos/Browserstack-logo.svg" alt="BrowserStack"/></a>
  
Development Discord
---
PHP-Fusion Official Developer Discord Channel - https://discord.gg/CGSYU8r

Those who are active on PHP-Fusion Development is encouraged to join. 
  
What's new on this repository management?
---
The latest repository contains all the previous commits done by all the core developers.
- 9.01 is beta 1
- 9.02 is beta 2
- Removed 9.1 branch
- Protected 7.02.07 branch from branch removal
- Submodules added to /infusions

New Changes: About Submodules and Git Management of Core Infusions (CI)
===
As of latest update on code management, the core team will have our <a href='https://github.com/php-fusion/PHP-Fusion/tree/Andromeda-9.0.3/infusions'>Latest Core Infusions</a> to see them as a submodules. Submodules are alias that are being linked from each CI repositories now being for example,
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

New Core Team Project Colloboration
====
The project colloboration is now available at https://github.com/orgs/php-fusion/projects/1
All issues are tied to the project for team colloboration, with assigned tasks based on volunteering effort or best person which has been closest tied to such issue. The Kanban cards are useful for fast turnover and clear monitoring of all task that blocks the project from being completed and we will work based on fixing that identified blockage.

All Core Developers are given Admin Access for Read and Write. So feel free to add any critical issues that is critical to address for the completion of the project. 

We will be preparing Andromeda for public launch as soon as the Kanban Cards are done.

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
=======
>>>>>>> parent of bb68a122c... Merge branch 'Andromeda-9.0.3' into Babylon-9.0.4

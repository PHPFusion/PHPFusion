Widget Development
====
Released: PHP-Fusion Babylon

Description
==
Enabling widget blocks in back end administration panel to provide a total
overview of the entire web system depending on the infusions installed. Template customization
is enabled on Admin Themes, and widgets via the Template path mutation method without any need for php modification.

Welcome to the HTML era for PHP-Fusion. Say hello to MVT.

The following provides a full overview of methods to develop admin dashboard widgets.

**Location**

The folder for widgets will be located at /administration/dashboard/

**Structure Format for Widgets**

The format for dashboard widgets is /directory_name/directory_name.php
Namespace for hook is "dashboard_widgets"

**Enabling Dashboard in your Admin Theme**

Add this line into your theme after opentable();
```$xslt
new AdminDashboard();
```

**Features in this Development Version**
1. **Performance:** $_SESSION based to store admin's preference without the need for external table records
to ensure that everything is rendered fast.
2. **Drag and Drop:** To be enabled using JqueryUI methods.
3. **Widgets in development**
4. Checkboxes to enable and disable admin widgets.
5. We will use opensidex() for this purpose of rendering each widgets.



**Widgets in Development**
 
1. **Summary Widgets** a widget giving you the recent publication for all Core Infusions (CIs), latest comments and ratings.
2. **Welcome widget** to give first time user a good overview of whats' going on with PHP-Fusion 9.
4. **Submissions widget**


 







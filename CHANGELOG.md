# Babylon Change Logs
This document is intended to updated by **all developers** on and with regards to major changes of the PHPFusion Core.

##### Lists of confirmed API changes for PHPFusion Babylon.

| Subject | Change Descriptions |Function | Affected Areas |
|---|---|---|---|
|Sub admin rights|With addition of admin rights, adding double underscore after parent rights will also be considered inherited from parent rights. Adding "Add News" as (N__1) will be considered child for News with a checkrights of a "N" | - | -checkrights(), -pageAccess(), -Admin Panel Theme SDK, -Infusion Install must not install any parent package with double underscore "__" |

## Core Hooks Definitions
#####Back End Admin Panel on load

|Hook Namespace | Description | Location |
|---|---|---|
|adminpages | Add admin protocols to admin panel. | Runs at admin_header

#####Front End Runtime on load


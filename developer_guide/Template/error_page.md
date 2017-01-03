#Error Page Template
Last Updated: 2.Jan.2017

### Function Name: display_error_page(array $info)
**File Source**: /themes/templates/global/error.php
 
|  Macro Names | Description |  Folder |
|---|---|---|
|  {%title%}     |  Title     |
|  {%message%}     |  Message     |
|  {%image_src%}     |  Error Image Source     | images/error/ |
|   {%error_code%}  |   The Error Code  |
|   {%back_link%}   |   Url to the Main Page Link |
|   {%back_title%}  |   Locale for Main Page Link |

Template Tutorial:
---
1. Go to your active theme folder. i.e. /themes/FusionTheme/theme.php
2. Anywhere in the file, insert the following code:
````$xslt
function display_error_page(array $info = array()) {
        // You can modify the content below
        opentable('<i class=\'fa fa-warning fa-fw m-r-5 text-warning\'></i>{%title%}');
        // HTML 
        ?>
        <div class='row spacer-sm'>
            <div class='col-xs-12 col-sm-3 text-center'>
                <img class='img-responsive' src='{%image_src%}' alt='{%title%}' border='0' />
            </div>
            <div class='col-xs-12 col-sm-9'>
                <span class='va' style='height:160px'></span>
                <span class='va'>
                    <h4>{%error_code%}</h4>
                <div>{%message%}</div>
                <div class='spacer-sm'><a class='button' href='{%back_link%}'>{%back_title%}</a></div>
            </span>
            </div>
        </div>
        <?php
        // End of HTML 
        closetable();
````
4. Save the file and refresh. Your site will now parse the HTML instead of the stock default ones.
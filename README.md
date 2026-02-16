# Ringotel Integration for FusionPBX

https://github.com/user-attachments/assets/cab7ccd9-1b0b-4d2e-9a7f-adf9d9a02955

## Ringotel Install

Install Ringotel from the command line on your server
```
cd /var/www/fusionpbx/app
git clone https://github.com/fusionpbx/fusionpbx-app-ringotel.git ringotel
git config --global --add safe.directory /var/www/fusionpbx/app/ringotel
php /var/www/fusionpbx/core/upgrade/upgrade.php --permissions
php /var/www/fusionpbx/core/upgrade/upgrade.php
```

**Customize the display name** (Change `<Your Display Name>` to what you want it to be)
```
sed -i "s/\$apps\[\$x\]\['name'\] = \"Ringotel\";/\$apps\[\$x\]\['name'\] = \"<Your Display Name>\";/" /var/www/fusionpbx/app/<your-folder-name>/app_config.php
```

Optional: Ringotel could be installed into a different directory such as rt.
```
git clone https://github.com/fusionpbx/fusionpbx-app-ringotel.git rt
git config --global --add safe.directory /var/www/fusionpbx/app/rt
```
The menu would need to edited to use the custom directory name.

**Ringotel Website**
- Menu -> Integrations -> API Settings
  - Webhook URL: your.domain.com/app/ringotel/webhook.php
- Create Key
- Save the key to use later

**Your FusionPBX Server**
- Default Settings
  - Category: ringotel
  - Subcategory: ringotel_token
  - Value: Save the API Key here
  

## 1\. Ringotel Overview

The Ringotel Feature Side App integrates with FusionPBX to help administrators:

*   **Manage Extensions**: Control FusionPBX extensions.
    
*   **Bind Extensions to Ringotel**: Connect and synchronize extensions with Ringotel.
    
*   **Monitor Ringotel Status**: View Ringotel status for extensions within FusionPBX.

*   **Bandwidth Integration**: Integrate with Bandwidth with exist ringotel users.
    
*   **Call Parks**: Enable and manage call parking functionality directly within the Ringotel environmen.

This streamlines user management for those using both FusionPBX and Ringotel.

**In Integration tab, we currently have the Bandwidth integration feature. We may add more options later, or you can add new ones yourself.**

#### **Before accessing the Ringotel application page**, **update permissions and default settings** in FusionPBX. This ensures proper user access and module functionality.

![ringotel default settingsd](https://github.com/user-attachments/assets/8437e08e-6f79-4fe2-8857-fd9a80068e99)


## 2. Ringotel Extensions List App
![ringotel_extension_list_example](https://github.com/user-attachments/assets/b513449b-c6ff-4b5d-9abd-d71345bff1ae)

This feature adds a "Ringotel status of user" column to FusionPBX's `extensions.php` page.

To enable it, **append the following line** to your `extension.php` file (typically in `app/extensions/`):
```
    //include the ringotel extension list
    require_once dirname(__DIR__, 2)."/app/ringotel/ringotel_extension_list.php";
```
![ringotel_extension_list](https://github.com/user-attachments/assets/be0f82c7-8696-402a-ab2c-6a99d7af0282)

The new column will display statuses and user's information providing quick insight into each extension's Ringotel integration.

#### Example how to enable the "Ringotel Extensions List App":
https://github.com/user-attachments/assets/6508c37c-6533-4128-9ce2-a8be88b562d4

 
 

***

# PHPFusion 10: Development Architecture Guide
**Version:** 10.0 (Unified REST API Standard)

## 1. Overview
PHPFusion 10 introduces a decoupled architecture where business logic is separated from the presentation layer (Themes/Panels). This allows the core system to serve both traditional web users and external interfaces (Mobile Apps, AJAX, IoT) using a single "Source of Truth."

---

## 2. Implementation Flows

### Pattern A: Internal Core/Admin Logic
Used when a standard `.php` file (like `administration/settings.php`) needs to process data. It interacts directly with the **Service Layer**.

**Flow:**
`User Form` → `Sanitization` → `Service Layer` → `Database`

**Example:**
```php
// Standard Web Admin Page logic
use PHPFusion\Administration\Api\Services\SettingsService;

if (isset($_POST['savesettings'])) {
    if (fusion_safe()) {
        try {
            // Direct Service Call
            $service = new SettingsService();
            $service->updateMainSettings($_POST);
            
            addnotice('success', $locale['900']);
            redirect(FUSION_REQUEST);
        } catch (Exception $e) {
            addnotice('danger', $e->getMessage());
        }
    }
}
```

---

### Pattern B: External REST API
Used for Mobile Apps or third-party integrations. This flow passes through the **Router** and **Middleware** for security.

**Flow:**
`External Request` → `api.php` → `Router` → `Middleware` → `Controller` → `Service`

**Example Controller (`administration/api/Controllers/SettingsController.php`):**
```php
namespace PHPFusion\Administration\Api\Controllers;

class SettingsController {
    public function update($request) {
        $service = new \PHPFusion\Administration\Api\Services\SettingsService();
        $service->updateMainSettings($request['body']);
        
        return [
            'status' => 'success',
            'message' => 'Settings updated via API'
        ];
    }
}
```

---

### Pattern C: Asynchronous AJAX
Used for modernizing the PHPFusion 10 UI. JavaScript calls the internal API to update components without a page refresh.

**Example JS:**
```javascript
async function saveConfig(data) {
    const response = await fetch('api.php?route=admin/settings/update', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    });
    return await response.json();
}
```

---

## 3. Directory & Namespace Mapping
PHPFusion 10 uses a segmented autoloader to map namespaces to their respective core directories.

| Layer | Namespace Segment | Physical Path |
| :--- | :--- | :--- |
| **Services** | `PHPFusion\Administration\Api\Services` | `administration/api/Services/` |
| **Controllers** | `PHPFusion\Administration\Api\Controllers` | `administration/api/Controllers/` |
| **Middleware** | `PHPFusion\Rest\Middleware` | `includes/classes/PHPFusion/Rest/Middleware/` |
| **Core API** | `PHPFusion\Api` | `includes/classes/Api/` |

---

## 4. Coding Standards

1. **Services:** All SQL `INSERT`, `UPDATE`, and `DELETE` queries must reside in a Service class. Services should throw `Exceptions` rather than echoing errors.
2. **Controllers:** Controllers should only handle the "translation" between JSON/Request data and the Service. They should not contain SQL.
3. **Middleware:** Use Middleware for Auth checks (`iADMIN`, `iMEMBER`) to keep your Controllers clean.
4. **Router:** Register all API endpoints in `administration/routes.php` (Admin) or `includes/api/routes.php` (Public).

---

## 5. Security Requirements
* All API requests must be validated through the `AdminAuth` or `MemberAuth` middleware.
* Input must be sanitized using the native `sanitizer()` function before being passed to a Service.
* API responses must always be valid JSON.
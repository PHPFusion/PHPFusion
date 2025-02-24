# TEMPLATES Folder Documentation

## Overview
This system dynamically loads template files based on the current browser URL by matching it to files within the `TEMPLATES/{sitepath}` directory. It supports both direct file matches and directory-style routing.
This template system works with the Polaris.autoloader.php file. The system overrides the global defaults which was placed on the PHPFusion 9 system. This allows for flexible and dynamic content loading based on user requests.

## Directory Structure
```
TEMPLATES/
├── {sitepath}/
│   ├── index.php  # Default fallback template
│   ├── about.php  # Matches `/about`
│   ├── about/
│   │   ├── team.php  # Matches `/about/team`
│   │   ├── index.php  # Matches `/about/`
│   ├── contact.php  # Matches `/contact`
│   ├── contact/
│   │   ├── index.php  # Matches `/contact/`
```

## How It Works
1. The system retrieves the current URL path and trims leading slashes.
2. It breaks the URL into segments to determine the corresponding template file.
3. The system attempts to match the URL to a file in `TEMPLATES/{sitepath}/` by:
   - Checking if the last URL segment has a file extension (e.g., `.php`).
   - If not, assuming it's a directory and looking for an `index.php` inside it.
4. If no match is found, it falls back to `TEMPLATES/{sitepath}/index.php`.

## Matching Rules
- `/about` → Loads `TEMPLATES/{sitepath}/about.php` (if exists) or `TEMPLATES/{sitepath}/about/index.php`
- `/about/team` → Loads `TEMPLATES/{sitepath}/about/team.php` or `TEMPLATES/{sitepath}/about/team/index.php`
- `/contact` → Loads `TEMPLATES/{sitepath}/contact.php` or `TEMPLATES/{sitepath}/contact/index.php`
- `/` (root) → Loads `TEMPLATES/{sitepath}/index.php`

## Adding New Templates
To add new templates, follow these rules:
- If you want a direct match for `/page-name`, create `TEMPLATES/{sitepath}/page-name.php`.
- If you want `/page-name/` to act as a directory, create `TEMPLATES/{sitepath}/page-name/index.php`.
- For deeper nested routes like `/parent/child`, use `TEMPLATES/{sitepath}/parent/child.php` or `TEMPLATES/{sitepath}/parent/child/index.php`.

## Error Handling
- If no matching template is found, the system displays a "Template file not found" message.
- Ensure all templates follow proper naming conventions to avoid errors.

## Exclusions of /images/assets/ folder from the repository
- What PHPFusion.com will be using might contain commercial copyright, including images/videos/glyphs/etc that was found from internet that could violates respective copyright holders.
- However, all of the artwork will be regenerated during production phase and credits will be given to copyright holders (copyleft) if any copyrighted materials are used.
- In order to save the developer's team from the risk of legal issues, we exclude the `/images/assets/` folder from the repository. This ensures that only necessary files are included in the project.
- You can however, find the contents online and add them back yourself, but that will be on your own risk and responsibility. Our team just do not want to be caught by tiny legal prints texts!

## Notes
- This system assumes all templates are PHP files.
- The `{sitepath}` variable is derived from `fusion_get_settings('site_path')`.
- If additional routing logic is needed, it can be modified within the script.

This README serves as a guide for maintaining and expanding the template system efficiently.


# PHPFusion 10 Platform Introduction

## Document purpose

This document introduces the current PHPFusion 10 platform and its built-in capabilities. It is written as a source brief for product documentation, presentations, and AI-assisted landing-page generation.

The private application under `infusions/school/` is deliberately excluded. No feature, workflow, data model, or product claim in this document depends on that project.

## Product summary

PHPFusion 10 is a modular, self-hosted web platform for building content-rich sites, administration systems, communities, and custom business applications. It combines a mature PHP CMS foundation with a modern administration interface, a native component and form SDK, API-oriented services, AI-assisted editing, live development previews, and an installable infusion ecosystem.

The platform is designed for teams that want the speed of an integrated CMS without giving up control of their source code, database, deployment, themes, extensions, or user data.

### Short positioning statement

> A self-hosted PHP platform where content, administration, AI tools, themes, and application modules work as one extensible system.

### One-sentence value proposition

PHPFusion 10 helps developers and administrators create, extend, secure, and operate modern web applications from one modular platform, with native UI tools, AI service connections, live previews, and one-click module activation.

## Platform foundations

- **Self-hosted and source-controlled:** Application code, themes, infusions, assets, and data integrations remain under the owner's control.
- **PHP-native architecture:** The platform runs on PHP with MySQL-compatible database drivers and server-rendered pages, while supporting modern JavaScript interfaces where they add value.
- **Modular by design:** Features can be added through infusions, themes, widgets, hooks, routes, middleware, services, custom fields, and reusable UI helpers.
- **Administration-first:** A complete administration center covers users, permissions, content, themes, plugins, security, updates, logs, backups, and system configuration.
- **Progressive modernization:** Traditional PHP pages can coexist with REST endpoints, AJAX interactions, rich editors, server-side data tables, and modern build tooling.

## Major feature pillars

### 1. AI service sockets and agent SDK

PHPFusion 10 includes an AI integration layer for connecting application workflows to external AI services. The current implementation provides server-side AI endpoints, reusable prompt files, structured agent identity cards, request helpers, and editor actions that can send live field content and contextual payloads to an AI service.

The term **AI socket** describes a replaceable connection point between a PHPFusion feature and an AI provider. A socket can accept a task, system instructions, live form data, and supporting documents, then return a response to the calling interface. This keeps AI behavior connected to platform permissions and application context instead of isolating it in a separate chatbot.

Current AI capabilities include:

- Server-side AI requests through PHP and cURL.
- API endpoints dedicated to AI-assisted actions.
- Reusable prompt templates for task-specific instructions.
- Structured JSON agent cards for identity, tone, expertise, and operational constraints.
- Multi-agent role selection through named agent profiles.
- Context-aware editor actions that can submit one field or a mapped set of fields.
- Support for fixed contextual metadata and live JavaScript payload builders.
- In-interface AI response areas, loading states, action buttons, and result handling.
- Hook-based endpoint extension so infusions can register additional AI operations.

The checked implementation currently contains a Cohere-compatible adapter. The surrounding endpoint, prompt, payload, and agent-card structure provides the foundation for adding further provider adapters without redesigning every calling feature.

#### Good landing-page language

> Connect intelligent services directly to the work your administrators already do. PHPFusion AI sockets carry live context, structured prompts, and task-specific agent behavior between your interface and supported AI providers.

### 2. Live change and content previews

The platform supports several forms of immediate feedback:

- **Development live reload:** Vite watches PHP and LESS sources and refreshes the browser after changes.
- **Rendered content preview:** Dynamics can send editor content to a protected AJAX preview endpoint and render it using PHPFusion's own text parsing rules.
- **Theme sessions:** A selected theme can be applied through session state for previewing without immediately changing the global theme.
- **Persistent editor drafts:** Textarea content can be stored in the active session and restored during the editing workflow.
- **Asynchronous administration:** Internal APIs allow components and settings to update without requiring a complete page reload.

Teams working against the same development or staging environment can review the latest rendered changes together as the shared environment refreshes. This is a shared-preview workflow, not currently a synchronized document-collaboration engine.

The repository does not currently show synchronized cursors, administrator presence indicators, operational transforms, CRDT conflict resolution, or simultaneous co-editing. Landing-page copy should use **live preview**, **shared preview**, or **review changes together**, and should not claim Google Docs-style real-time co-authoring unless that layer is added later.

### 3. Infusions: one-click application modules

Infusions are PHPFusion's installable application modules. They are comparable to packages in a software ecosystem: each infusion can declare its metadata, database changes, administration pages, site links, locale files, permissions, and upgrade steps.

From the administration center, an authorized user can activate, upgrade, or remove a discovered infusion. The installer understands module declarations for:

- New, altered, and removed database tables.
- New and removed columns and indexes.
- Seeded, updated, and removed database rows.
- Administration navigation and access rights.
- Public site links.
- Multilingual records and administration entries.
- Versioned upgrade files.
- Module settings and database constants.
- Controlled cleanup during removal.

Infusions are versioned independently from the core. This gives module authors freedom to release updates on their own lifecycle while retaining a standard installation contract.

PHPFusion also links administrators to the community infusion marketplace. The current local installer activates modules already present on the server; remote marketplace download and dependency resolution should not be described as an npm-compatible registry unless a dedicated package transport is added.

#### Good landing-page language

> Add new capabilities without rebuilding the core. PHPFusion infusions can register data, permissions, administration pages, routes, settings, and upgrades through one native module contract.

### 4. Fusion Native Editor powered by Tiptap

The new native editing experience is built on Tiptap 3 and ProseMirror foundations. It is integrated into PHPFusion Dynamics rather than treated as a disconnected third-party field.

Implemented editor capabilities include:

- Bold, italic, strike-through, inline code, and clear formatting.
- Headings, paragraphs, block quotes, bullet lists, numbered lists, and code blocks.
- Underline, highlight, typography improvements, subscript, and superscript.
- Nested task lists and task items.
- Contextual bubble menus.
- Placeholder text.
- User or entity mentions with a reusable suggestion menu.
- Markdown input and serialization.
- Synchronization back to the original form textarea.
- Standard `change` event dispatch so existing PHPFusion form and AI handlers continue to work.
- Per-field editor instances exposed to approved page scripts.
- Session-backed draft persistence.
- AI action integration through `elite_textarea()`.

The editor is distributed as a compiled local asset, which keeps production editing independent of a public CDN. The npm manifest also provides the source dependency list and an esbuild-based bundling path.

The platform retains CKEditor 5, TinyMCE, TinyMCE 5, and Quill assets for compatibility and migration. Tiptap is the forward-looking native editor path.

### 5. Dynamics UI and form SDK

Dynamics is PHPFusion's server-side UI SDK. It gives developers a consistent way to create validated, secure, Bootstrap-compatible forms without manually rebuilding labels, errors, assets, and client behavior for every screen.

The current Dynamics component set includes:

- Form opening and closing with automatic CSRF tokens and honeypots.
- Text, number, price, password, email, URL, telephone, IP, date, time, and search inputs.
- Standard and AI-enabled textareas.
- Tiptap and legacy editor integration.
- Select menus, searchable Select2 controls, user selectors, tree selectors, and chained selects.
- Checkboxes, radio-style groups, button groups, hidden fields, and range controls.
- Date and time pickers with locale-aware Flatpickr assets.
- File and document uploads with previews and validation.
- Color pickers.
- Contact and international calling-code inputs.
- Geographic and location selectors.
- Ordering controls.
- Modal forms.
- Input masks, password-strength feedback, append/prepend actions, floating labels, tooltips, and responsive sizing.
- Reusable validation feedback connected directly to Defender.

Dynamics loads supporting assets only when a component needs them. This keeps the PHP API simple while allowing controls such as Select2, Flatpickr, file input, autosize, color picker, password strength, and switch controls to remain modular.

### 6. Defender security framework

Defender is the platform's integrated request and form protection layer. Security behavior is built into the same helpers developers use to render forms and receive input.

Current Defender capabilities include:

- HMAC-based CSRF token generation and verification.
- Per-page and per-form token rings.
- Automatic POST validation.
- Honeypot fields and automated bot rejection.
- Centralized input errors and localized validation messages.
- Validation rules for text, passwords, email, price, numbers, dates, URLs, names, addresses, contacts, checkboxes, media paths, files, documents, and images.
- Upload and MIME inspection.
- Image validation and safer file handling modes.
- Callback and regular-expression validation support.
- Request sanitization helpers used throughout forms and APIs.

Additional platform security includes:

- Authentication and password-hash management.
- Session-bound administration authorization tokens.
- Optional database-backed sessions.
- User levels, groups, and granular administration rights.
- Blacklists, flood control, registration gateways, honeypots, and CAPTCHA integrations.
- Google account login support.
- API middleware for protected administration routes.
- Error logging, user activity logs, and security settings in the administration center.

### 7. REST, AJAX, and service architecture

PHPFusion 10 supports three application patterns:

1. **Server-rendered PHP:** Standard pages call a service directly after sanitization.
2. **External REST:** Requests pass through a router, middleware, controller, and service.
3. **Internal AJAX:** JavaScript calls platform endpoints for asynchronous updates.

The architecture separates concerns so controllers translate requests, middleware enforces access, and services own persistent changes. This lets one business operation serve an administration page, a modern asynchronous component, or an external client without duplicating the underlying rules.

Available foundations include:

- GET and POST route registration.
- Controller callables and closures.
- Authentication middleware.
- JSON request and response handling.
- Public and administration route groups.
- Hook-registered legacy-style API endpoints.
- Service classes for reusable business operations.
- A server-side DataTable SDK for large searchable listings.

### 8. Extensible core and developer hooks

PHPFusion is designed to be extended at multiple levels:

- **Hooks and filters:** Register callbacks, choose execution priority, pass arguments, apply once, repeat, or remove callbacks.
- **Infusions:** Install complete modules with schema, navigation, rights, settings, and upgrades.
- **Themes and widgets:** Add presentation systems, presets, theme-specific data, and composable blocks.
- **Routes and middleware:** Expose protected internal or external interfaces.
- **Services and controllers:** Keep persistent operations reusable across interfaces.
- **Custom fields:** Add field categories and inputs through the Quantum Fields system.
- **Search drivers:** Extend global search with module-specific results.
- **Permalink drivers:** Add human-readable URL patterns and rewrites.
- **Task handlers:** Register background and recurring work through the task dispatcher and scheduler.
- **Locale overlays:** Add translated interface and module copy without hardcoding it into templates.

This is the platform's strongest npm-like quality: small capabilities can be composed through stable contracts. It should be described as **package-inspired extensibility**, not as literal npm package compatibility.

### 9. Theme, widget, and page composition system

The platform separates public themes from administration themes and supports theme metadata, screenshots, activation, presets, generated CSS, configuration records, and theme-specific widgets.

Current presentation capabilities include:

- Separate public and administration themes.
- Theme discovery and activation from the administration center.
- Theme presets stored in the database.
- Theme configuration editing and generated CSS.
- Session-based theme selection for previewing.
- Theme widgets with their own installation data and settings.
- Built-in widget types for blocks, comments, features, files, panels, ratings, and sliders.
- A page-composer foundation with widget interfaces, page models, controllers, views, content nodes, and settings nodes.
- Panel placement and visibility controls.
- Reusable templates, breadcrumbs, alerts, modals, navigation, and dashboard components.

The current Page Composer includes a working composition foundation. Some richer block ideas listed in its development notes remain future work and should not be marketed as completed drag-and-drop components.

### 10. Administration center

PHPFusion 10 includes a broad operations console rather than only a content editor.

Administrators can manage:

- Members, administrators, groups, profiles, and custom user fields.
- Granular administrator rights and navigation links.
- Registration, password, privacy, security, message, language, time, and site settings.
- Public site links, panels, banners, comments, BBCode, smileys, and custom pages.
- Themes, theme settings, presets, and widgets.
- Infusion installation, upgrades, and removal.
- File management and image assets.
- Database backups and migration tools.
- Permalinks and search-friendly routes.
- Robots configuration and site metadata.
- Email configuration.
- Error logs, user logs, PHP information, and server information.
- Core and language update checks.

The active administration UI uses Bootstrap 5 and Tabler-oriented assets, with responsive navigation, icons, charts, enhanced selects, scroll areas, and dashboard components.

### 11. Content, community, and identity services

The core includes reusable services for building public sites and member applications:

- User registration, authentication, profiles, account settings, and password recovery.
- User groups, custom fields, access levels, and administration rights.
- Private messaging with inbox, sent, archive, reply, mark, and delete workflows.
- Comments with permissions, moderation, editing, and reusable display parameters.
- Notifications with unread retrieval and read-state updates.
- Global search with loadable search drivers.
- Panels, site links, custom pages, ratings widgets, and feedback components.
- Locale-aware language switching and translated country/language names.
- Open Graph metadata for pages and user profiles.
- Clean URL and permalink rewriting.
- Email delivery through PHPMailer.

### 12. Data, files, documents, and integrations

Platform-level integration capabilities include:

- MySQLi and PDO MySQL database drivers.
- File, Memcache, and Redis cache backends.
- Database-backed sessions.
- File and image upload helpers with thumbnails and modern image formats.
- elFinder-based file management with sanitizer, normalizer, auto-rotate, auto-resize, and watermark plugins.
- PDF generation through mPDF and FPDI.
- Google API client support.
- JWT and OAuth-related dependencies through the Google authentication stack.
- Guzzle HTTP clients and PSR HTTP interfaces.
- Monolog logging support in the Composer dependency set.
- Scheduled and recurring tasks with status, failure recording, and dispatcher priorities.
- CSS and JavaScript minification utilities.
- A bundled LESS compiler and Vite/esbuild development tooling.

## DataTable SDK

The Fusion DataTable SDK standardizes large administration and frontend listings. It wraps DataTables initialization and server-side query responses.

Supported capabilities include:

- Server-side processing and pagination.
- Global search and field-specific filters.
- SQL-aware sorting and search maps.
- Responsive tables.
- Copy, Excel, PDF, and column visibility controls.
- Saved table state.
- Row reordering with a persistence endpoint.
- Column resizing and reordering.
- Fixed headers.
- Render callbacks for badges, actions, and formatted values.

This gives module authors a standard path from a PHP query to a responsive, searchable operational table.

## Extension and plugin inventory

The word **plugin** covers several layers in this platform. Landing-page copy should group them by purpose instead of presenting every library as a first-party product.

### Native PHPFusion extension types

- Infusions.
- Public themes.
- Administration themes.
- Theme widgets.
- Panels.
- Hooks and filters.
- API routes and middleware.
- Search and permalink drivers.
- Quantum custom-field plugins.
- Scheduled task handlers.
- Login connectors.

### Bundled non-private infusions and panels

- Language selection panel.
- Licensing server infusion with license records, orders, certificates, activation endpoints, key validation, and administration views.

### Editor stack

- Tiptap 3 native editor path.
- ProseMirror foundation through Tiptap.
- Tiptap Markdown.
- CKEditor 5 compatibility assets.
- TinyMCE and TinyMCE 5 compatibility assets and plugins.
- Quill 2 compatibility assets.

### UI and visualization libraries

- Bootstrap 5, with legacy Bootstrap 3 and 4 compatibility assets.
- Tabler UI and Tabler Icons.
- Popper.
- DataTables 2.
- ApexCharts.
- FullCalendar.
- jsVectorMap.
- Leaflet.
- Choices.js.
- Select2.
- Flatpickr.
- Swiper.
- Splide.
- SimpleBar.
- Sticky Sidebar.
- File Input.
- Autosize.
- Color picker, switch, password-strength, input-mask, and chained-select helpers.

### Build and package tooling

- Composer with a project-local `includes/vendor/` directory.
- npm dependency manifest.
- Vite development server and live reload.
- esbuild for editor bundles.
- LESS compilation.
- CSS and JavaScript minification.

## Capability status and claim safety

Use this table when generating public-facing copy.

| Capability | Status in current repository | Safe public claim |
|---|---|---|
| PHPFusion 10 API architecture | Implemented foundation | REST, AJAX, middleware, controllers, and services |
| AI service connection | Implemented with a current provider adapter | Connect contextual workflows to supported AI services |
| AI agent cards and prompts | Implemented | Build task-specific AI assistants with structured roles |
| AI actions inside editors | Implemented | Ask AI to improve or process live form content |
| Tiptap native editor | Implemented and locally bundled | Modern native rich-text and Markdown editing |
| Session draft persistence | Implemented | Recover in-progress editor content during the session |
| Rendered content preview | Implemented | Preview parsed content before publishing |
| Vite live reload | Implemented for development | See code and style changes refresh quickly |
| Shared administrator review | Supported through a shared environment | Review the latest preview together |
| Simultaneous co-editing | Not evidenced | Do not claim synchronized co-authoring yet |
| Infusion activation and upgrades | Implemented | Activate and upgrade modular add-ons from administration |
| Remote npm-style dependency registry | Not implemented as such | Describe package-inspired extensibility, not npm compatibility |
| Theme presets and widgets | Implemented | Configure themes, presets, and installable theme widgets |
| Page Composer | Foundation implemented | Extensible page and widget composition foundation |
| Defender security | Implemented | Integrated CSRF, honeypot, validation, and upload protection |
| DataTable SDK | Implemented | Build responsive server-side operational tables |
| Background scheduling | Implemented | Register scheduled, recurring, and prioritized tasks |
| Multi-backend cache | Implemented | Use file, Memcache, or Redis caching |

## Recommended landing-page narrative

### Hero

**Headline:**

> PHPFusion 10

**Supporting copy:**

> A modular, self-hosted web platform with native administration, AI service sockets, live previews, modern editing, and an extensible infusion ecosystem.

**Primary action:** Explore the platform

**Secondary action:** View developer capabilities

### Section order

1. **One platform, many applications**
   Explain that the core can power content sites, communities, portals, administration systems, and custom applications.

2. **AI that works inside your workflow**
   Show AI sockets, prompt templates, agent cards, contextual payloads, and editor actions.

3. **Edit, preview, and review faster**
   Show the Tiptap editor, session drafts, parsed content preview, theme preview, and live development refresh.

4. **Extend it through infusions**
   Show one-click activation, module metadata, permissions, schema installation, routes, settings, and independent upgrades.

5. **Build consistent interfaces with Dynamics**
   Show forms, uploads, selects, dates, location controls, validation, modals, and AI-enabled textareas.

6. **Security built into the form layer**
   Show Defender tokens, validation, upload checks, honeypots, access rights, flood protection, and API middleware.

7. **A complete administration center**
   Show users, content, themes, plugins, logs, backups, settings, and updates in one operational interface.

8. **Developer-ready foundations**
   Show REST patterns, hooks, services, tasks, cache drivers, DataTables, Composer, npm, Vite, LESS, and local asset builds.

9. **Own the platform**
   Close on self-hosting, source control, deployment freedom, and extensibility.

### Suggested proof points

- Native Tiptap 3 editor with Markdown, mentions, task lists, and AI actions.
- Defender protection integrated into every Dynamics form.
- Three development modes: server-rendered PHP, REST, and internal AJAX.
- Installable infusions with schema, permissions, administration pages, and versioned upgrades.
- File, Memcache, and Redis cache options.
- MySQLi and PDO MySQL database drivers.
- Server-side DataTable SDK for large operational datasets.
- Vite live reload and local JavaScript bundles.
- Public and administration themes with presets and widgets.

## Tone and visual direction for an AI-generated website

- Present PHPFusion 10 as a capable platform, not as a generic website template.
- Use a quiet, technical, product-focused interface with visible administration and editor screens.
- Lead with the actual platform name and an authentic interface image or product capture.
- Use diagrams or interface callouts for AI sockets, Dynamics, Defender, and infusions.
- Keep claims concrete and operational.
- Avoid vague phrases such as "limitless innovation" or "revolutionary AI."
- Avoid implying that third-party libraries were created by PHPFusion.
- Avoid calling the system a JavaScript framework; PHP remains the application core.
- Avoid claiming simultaneous co-editing until presence and conflict-resolution features exist.
- Avoid describing the infusion marketplace as a full npm registry.

## Final product description

PHPFusion 10 is a self-hosted, extensible PHP platform for teams that need more than a page builder. It provides a complete administration environment, secure native forms, modern rich-text editing, AI-assisted workflows, modular infusions, themes and widgets, REST and AJAX foundations, operational data tools, background tasks, caching, localization, and a broad compatibility layer for established web libraries.

Its defining advantage is integration: Defender secures the same Dynamics controls developers use to build forms; the native editor can feed contextual AI actions; infusions can register data, rights, routes, and administration pages; themes and widgets share the same platform services; and both traditional pages and modern clients can call reusable application logic.

That makes PHPFusion 10 a practical foundation for building and operating custom web systems while keeping ownership, extensibility, and deployment control in the hands of the team using it.

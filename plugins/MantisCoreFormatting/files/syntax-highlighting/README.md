Syntax highlighting
===

This implementation is based on Prism.

- https://github.com/PrismJS
- https://prismjs.com/index.html


Files
---

https://github.com/PrismJS/prism/

Copy only the minimized versions of:

- plugins to ./plugins
- components to  ./components
- themes to ./themes


Plugins and Components
---

Some of the plugins has dependencies to other plugins or components which are not loaded automatically by default.

To solve this, the plugins and their dependencies are "registered" in the [init.js](init.js) that handles the proper loading in the right order.

If a plugin is added or removed, the `availableResources` array must be changed accordingly.


CDN
---

If the options `$g_cdn_enabled` is set to `ON`, the files are loaded from this CDN.

- https://cdnjs.com/libraries/prism/1.29.0
- https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/


More themes
---

Prism has another repository for themes, and these are also available via the same CDN, but under a different URL.

- https://github.com/PrismJS/prism-themes
- https://cdnjs.com/libraries/prism-themes/1.29.0
- https://cdnjs.cloudflare.com/ajax/libs/prism-themes/1.9.0/

They are not yet included, as this means dealing with two CDN URLs.

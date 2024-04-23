Syntax highlighting
===

This implementation is based on Prism.

- https://github.com/PrismJS
- https://prismjs.com/index.html

### List of supported languages

A list of all supported languages can be found here.

- https://prismjs.com/index.html#supported-languages


Files
---

https://github.com/PrismJS/prism/

Copy only the minimized versions of:

- plugins to ./plugins
- components to  ./components
- themes to ./themes


Plugins and Components
---

Some of the plugins have dependencies on other plugins or components which are not loaded automatically by default.

To solve this, the plugins and their dependencies are "registered" in the [init.js](init.js) that handles the proper loading in the right order.

If a plugin is added or removed, the `availableResources` array must be changed accordingly.


CDN
---

If the option `$g_cdn_enabled` is set to `ON`, the files are loaded from this CDN.

- https://cdnjs.com/libraries/prism/1.29.0
- https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/


Known Issues
---

The plugin "Show whitespaces" (show-invisibles) does not work well in combination with some languages.

1. The highlighting of the code is broken
2. Tabs are displayed as whitespaces (e.g. Indents)

The issue occurs at least with the languages "LISP" and "Dart".

However, this behavior is not consistent. There is the observation that if more than one of these code blocks are present on the same page, only the first one is affected, the second one works (except for the style of the whitespaces).

This could indicate that something in the order of execution does not match.

If you are interested, a fix would be very welcome.

- Plugin: https://prismjs.com/plugins/show-invisibles/
- Issue report: https://github.com/PrismJS/prism/issues/3789


More themes
---

Prism has another repository for themes, and these are also available via the same CDN, but under a different URL.

- https://github.com/PrismJS/prism-themes
- https://cdnjs.com/libraries/prism-themes/1.29.0
- https://cdnjs.cloudflare.com/ajax/libs/prism-themes/1.9.0/

They are not yet included, as this means dealing with two CDN URLs.

If you are interested, do not hesitate and open a pull request. It would be very welcome.

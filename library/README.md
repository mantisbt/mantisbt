# MantisBT external libraries

This directory contains a copy the 3rd-party libraries used by MantisBT.

The version and status of each is summarized below:

## PHP libraries

| directory  | project        | version | status      |
|------------|----------------|---------|-------------|
| rssbuilder | RSSBuilder     | 2.2.1   | patched [1] |


## Javascript/CSS libraries

| library / plugin            | version | status      |
|-----------------------------|---------|-------------|
| jquery                      | 2.2.4   | unpatched   |
| bootstrap                   | 3.3.6   | unpatched   |
| fontawesome                 | 4.7.0   | unpatched   |
| ace-admin theme             | 1.4.0   | customized  |
| moment.js                   | 2.29.4  | unpatched   |
| bootstrap-datetimepicker    | 4.17.47 | unpatched   |
| dropzone.js                 | 5.5.0   | unpatched   |
| chart.js                    | 3.9.1   | unpatched   |
| chartjs-plugin-colorschemes | 0.5.4   | unpatched   |
| typeahead.js                | 1.3.4   | unpatched   |
| list.js                     | 2.3.1   | patched [2] |

**Notes**

1. Next patches applied to rssbuilder:
   - removed `__autoload` function
   - fixed SYSTEM NOTICE 'Only variables should be passed by reference' (#25213)
   - fixed TypeError when creating empty feed on PHP 8 (#33634)
   - fixed deprecated PHP 8 warnings about dynamic properties and changed return types,
     removed trailing ?> (#35312)
2. Next patch applied to list.js:
   - fixed scrolling regression in navigation buttons (#30494), 
     patch submitted upstream https://github.com/javve/list.js/pull/750


## Upstream projects

| project                     | URL                                                           |
|-----------------------------|---------------------------------------------------------------|
| rssbuilder                  | http://code.google.com/p/flaimo-php/                          |
| jquery                      | https://jquery.com/                                           |
| bootstrap                   | http://getbootstrap.com/                                      |
| fontawesome                 | https://fontawesome.com/v4.7.0/                               |
| moment.js                   | https://momentjs.com/ - https://github.com/moment/moment/     |
| datetimepicker              | https://github.com/Eonasdan/bootstrap-datetimepicker          |
| dropzone.js                 | http://www.dropzonejs.com/ - https://github.com/enyo/dropzone |
| chart.js                    | http://www.chartjs.org/ - https://github.com/chartjs/Chart.js |
| chartjs-plugin-colorschemes | https://github.com/nagix/chartjs-plugin-colorschemes/         |
| typeahead.js                | https://github.com/corejavascript/typeahead.js                |
| list.js                     | http://listjs.com/ - https://github.com/javve/list.js         |

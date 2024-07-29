/*
 * Initialize the Prism syntax highlighting
 */
((script) => {
	const userSelectedPlugins = script
		.getAttribute('data-plugins')
		.split(',')
		.filter((plugin) => plugin !== '')
		.map((plugin) => plugin.trim());
	const cdn = script.getAttribute('data-cdn');
	const theme = script.getAttribute('data-theme');
	const i18n = JSON.parse(
		script.getAttribute('data-i18n')
	) ?? {};
	const head = document.getElementsByTagName('head')[0];
	const resourceUrl = 1 === parseInt(cdn)
		? 'https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0'
		: document.currentScript.src.replace('/init.js', '')
	;

	/*
	 * Available plugins/resources
	 *
	 * If new plugins are added or others are deleted, this must be
	 * reflected here. Only plugins that are listed here will be loaded.
	 */
	const availableResources = [{
		name: 'toolbar',
		type: 'plugin',
		requires: null,
		css: true,
		ready: false,
		cssClasses: null,
	}, {
		name: 'css',
		type: 'component',
		requires: null,
		css: false,
		ready: false,
		cssClasses: null,
	}, {
		name: 'css-extras',
		type: 'component',
		requires: 'css',
		css: false,
		ready: false,
		cssClasses: null,
	}, {
		name: 'line-numbers',
		type: 'plugin',
		requires: null,
		css: true,
		ready: false,
		cssClasses: ['line-numbers'],
	}, {
		name: 'match-braces',
		type: 'plugin',
		requires: null,
		css: true,
		ready: false,
		cssClasses: ['rainbow-braces', 'match-braces'],
	}, {
		name: 'show-language',
		type: 'plugin',
		requires: 'toolbar',
		css: false,
		ready: false,
		cssClasses: null,
	}, {
		name: 'copy-to-clipboard',
		type: 'plugin',
		requires: 'toolbar',
		css: false,
		ready: false,
		cssClasses: null,
	}, {
		name: 'inline-color',
		type: 'plugin',
		requires: 'css-extras',
		css: true,
		ready: false,
		cssClasses: null,
	}, {
		name: 'diff-highlight',
		type: 'plugin',
		requires: null,
		css: true,
		ready: false,
		cssClasses: ['diff-highlight'],
	}, {
		name: 'normalize-whitespace',
		type: 'plugin',
		requires: null,
		css: false,
		ready: false,
		cssClasses: null,
	}, {
		name: 'show-invisibles',
		type: 'plugin',
		requires: null,
		css: true,
		ready: false,
		cssClasses: null,
	}, {
		name: 'previewers',
		type: 'plugin',
		requires: 'css-extras',
		css: true,
		ready: false,
		cssClasses: null,
	}];

	const loadScript = (url, callback = null) => {
		const script = document.createElement('script');
		script.src = url;
		script.onload = callback;
		head.appendChild(script);
	};

	const loadCss = (url, callback = null) => {
		const css = document.createElement('link');
		css.rel = 'stylesheet';
		css.href = url;
		css.onload = callback;
		head.append(css);
	};

	const loadResource = (resource, callback) => {
		if ('component' === resource.type) {
			loadScript(
				`${resourceUrl}/components/prism-${resource.name}.min.js`,
				callback
			);
		} else {
			if (resource.css) {
				loadCss(
					`${resourceUrl}/plugins/${resource.name}/prism-${resource.name}.min.css`,
					callback
				);
			}
			loadScript(
				`${resourceUrl}/plugins/${resource.name}/prism-${resource.name}.min.js`,
				callback
			);
		}
	};

	const load = (resource) => {
		return new Promise(async (resolve) => {
			if (resource.requires) {
				const requirement = availableResources.find(
					(r) => r.name === resource.requires
				);
				if (!requirement.ready) {
					await load(requirement);
				}
			}

			loadResource(resource, () => {
				resource.ready = true;
				resolve();
			});
		});
	};

	const loadResources = (resources) => {
		return new Promise(async (resolve) => {
			for (const resource of resources) {
				await load(resource);
			}
			resolve();
		});
	};

	// Load the Prism theme CSS.
	loadCss(`${resourceUrl}/themes/${theme}`);

	/*
	 * Initialize Prism
	 *
	 * "Prism.manual" is needed to call "Prism.highlightAll();" manually after
	 * all files have been loaded serialized.
	 *
	 * @see https://prismjs.com/docs/Prism.html#.manual
	 */
	window.Prism = window.Prism || {};
	window.Prism.manual = true;
	document.addEventListener('DOMContentLoaded', () => {
		// Search for relevant <code> elements.
		const codeBlocks = document.querySelectorAll(
			'pre > code[class*="language-"]'
		);

		// Do not load anything if there is no relevant <code> element.
		if (0 === codeBlocks.length) {
			return;
		}

		// Filter the plugins selected by the user from the available plugins.
		const plugins = availableResources
			.filter((resource) => userSelectedPlugins.includes(resource.name));

		// Filter the plugins that requires additional CSS classes on "code" elements.
		plugins
			.filter((plugin) => plugin.cssClasses)
			.forEach((plugin) => {
				codeBlocks.forEach((code) =>
					code.classList.add(...plugin.cssClasses)
				);
			});

		// Add translations.
		const body = document.getElementsByTagName('body')[0];
		plugins
			.filter((plugin) => Object.keys(i18n).includes(plugin.name))
			.forEach((plugin) => {
				for (const [key, value] of Object.entries(i18n[plugin.name])) {
					body.setAttribute(`data-prismjs-${key}`, `${value}`);
				}
			});

		/*
		 * Load the chain serialized to avoid unexpected behaviour due
		 * different loading speed.
		 */
		loadScript(`${resourceUrl}/prism.min.js`, () => {
			loadResources(plugins).then(() => {
				loadResource({ name: 'autoloader' }, () => {
					// noinspection JSUnresolvedReference
					Prism.highlightAll();
				});
			});
		});
	});
})(document.getElementById('mantis-core-formatting-syntax-highlighting-init'));

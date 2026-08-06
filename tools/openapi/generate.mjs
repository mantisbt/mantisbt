#!/usr/bin/env node

import { mkdir, readFile, rm, writeFile } from 'node:fs/promises';
import { dirname, resolve } from 'node:path';
import { spawnSync } from 'node:child_process';
import Converter from 'openapi-to-postmanv2';
import YAML from 'yaml';

const repositoryRoot = resolve(import.meta.dirname, '../..');
const source = resolve(repositoryRoot, 'api/rest/mantisbt_openapi.yaml');
const defaultOutput = resolve(repositoryRoot, 'api/rest/generated');
const brunoToolsDirectory = resolve(repositoryRoot, 'tools/bruno');

function usage() {
  console.error('Usage: node tools/openapi/generate.mjs [--postman|--bruno|--docs] [--output DIRECTORY]');
}

function run(command, args) {
  const result = spawnSync(command, args, { cwd: repositoryRoot, stdio: 'inherit' });
  if (result.error) {
    throw result.error;
  }
  if (result.status !== 0) {
    throw new Error(`${command} exited with status ${result.status}`);
  }
}

function convert(openapi) {
  return new Promise((resolveConversion, rejectConversion) => {
    Converter.convert(
      { type: 'json', data: openapi },
      {
        folderStrategy: 'Tags',
        requestNameSource: 'Fallback',
      },
      (error, result) => {
        if (error) {
          rejectConversion(error);
        } else if (!result.result) {
          rejectConversion(new Error(result.reason || 'OpenAPI to Postman conversion failed'));
        } else {
          resolveConversion(result.output[0].data);
        }
      },
    );
  });
}

function configureCollection(collection, openapi) {
  const server = openapi.servers?.[0];
  const securityScheme = Object.values(openapi.components?.securitySchemes || {})
    .find((scheme) => (scheme.type === 'apiKey' && scheme.in === 'header')
      || (scheme.type === 'http' && scheme.scheme === 'bearer'));

  if (!server?.url || !securityScheme) {
    throw new Error('The OpenAPI definition must provide a server and either header API-key or Bearer authentication');
  }

  const authorizationHeader = securityScheme.type === 'apiKey'
    ? securityScheme.name
    : 'Authorization';

  collection.variable = [
    { key: 'url', value: server.url, type: 'string' },
    { key: 'token', value: '', type: 'string' },
  ];
  collection.auth = securityScheme.type === 'apiKey'
    ? {
      type: 'apikey',
      apikey: [
        { key: 'key', value: authorizationHeader, type: 'string' },
        { key: 'value', value: '{{token}}', type: 'string' },
        { key: 'in', value: 'header', type: 'string' },
      ],
    }
    : {
      type: 'bearer',
      bearer: [
        { key: 'token', value: '{{token}}', type: 'string' },
      ],
    };

  const updateRequest = (item) => {
    if (item.item) item.item.forEach(updateRequest);
    if (!item.request) return;

    if (typeof item.request.url === 'string') {
      item.request.url = item.request.url.replaceAll('{{baseUrl}}', '{{url}}').replace(server.url, '{{url}}');
    } else if (item.request.url?.raw) {
      item.request.url.raw = item.request.url.raw.replaceAll('{{baseUrl}}', '{{url}}').replace(server.url, '{{url}}');
    }
    if (item.request.url?.host) {
      item.request.url.host = item.request.url.host.map((part) => part === '{{baseUrl}}' ? '{{url}}' : part);
    }
    for (const header of item.request.header || []) {
      if (header.key.toLowerCase() === authorizationHeader.toLowerCase()) {
        header.value = securityScheme.type === 'http' ? 'Bearer {{token}}' : '{{token}}';
      }
    }
  };
  collection.item.forEach(updateRequest);
}

const args = process.argv.slice(2);
let output = defaultOutput;
let postmanOnly = false;
let brunoOnly = false;
let docsOnly = false;
for (let index = 0; index < args.length; index += 1) {
  if (args[index] === '--output' && args[index + 1]) {
    output = resolve(repositoryRoot, args[++index]);
  } else if (args[index] === '--postman') {
    postmanOnly = true;
  } else if (args[index] === '--bruno') {
    brunoOnly = true;
  } else if (args[index] === '--docs') {
    docsOnly = true;
  } else {
    usage();
    process.exit(2);
  }
}
if ([postmanOnly, brunoOnly, docsOnly].filter(Boolean).length > 1) {
  usage();
  process.exit(2);
}

try {
  run('redocly', ['lint', source]);
  const openapi = YAML.parse(await readFile(source, 'utf8'));

  if (!docsOnly && !brunoOnly) {
    const collection = await convert(openapi);
    configureCollection(collection, openapi);
    const postmanOutput = resolve(output, 'mantisbt.postman_collection.json');
    await mkdir(dirname(postmanOutput), { recursive: true });
    await writeFile(postmanOutput, `${JSON.stringify(collection, null, 2)}\n`);
  }

  if (!postmanOnly && !brunoOnly) {
    const docsOutput = resolve(output, 'docs/index.html');
    await mkdir(dirname(docsOutput), { recursive: true });
    await rm(docsOutput, { force: true });
    run('redocly', ['build-docs', source, '--output', docsOutput]);
  }

  if (!postmanOnly && !docsOnly) {
    run('npm', ['ci', '--prefix', brunoToolsDirectory]);
    run('npm', ['exec', '--prefix', brunoToolsDirectory, '--', 'bru',
      'import', 'openapi', '--source', source, '--output', output,
      '--collection-format', 'bru', '--collection-name', 'bruno', '--group-by', 'tags',
    ]);
  }
} catch (error) {
  console.error(`OpenAPI generation failed: ${error.message}`);
  process.exitCode = 1;
}

# Building vue components for use with moodle plugins (build as UMD modules).

## Pre-reqs

You will need to have vue-cli and vue-cli-service installed:

npm install -g vue-cli
npm install -g @vue/cli-service

## Example of how to build a component:

If we have a component with the name th-Filter, cd to the components folder and run this from the command line:

vue-cli-service build --target lib --name th-Filter th-Filter.vue

The component will be built in the dist folder within the components folder.

e.g. 



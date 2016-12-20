/* jshint node:true */
//https://github.com/kswedberg/grunt-version
module.exports = {
    options: {
        pkg: {
            version: '<%= package.version %>'
        }
    },
    project: {
        src: [
            'package.json'
        ]
    },
    style: {
        options: {
            prefix: 'Version\\:\\s'
        },
        src: [
            'pirate-forms.php',
            'css/front.css',
        ]
    },
    functions: {
        options: {
            prefix: 'PIRATE_FORMS_VERSION\'\,\\s+\''
        },
        src: [
            'pirate-forms.php',
        ]
    }
};
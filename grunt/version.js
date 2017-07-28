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
            'public/css/front.css',
        ]
    },
    functions: {
        options: {
            prefix: 'version\\s+=\\s+[\'"]'
        },
        src: [
            'includes/class-pirateforms.php',
        ]
    }
};
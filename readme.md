# View template system #

A simple template system which supports:

- set/get for setting variables
- allows '$this' keyword in the template
- setting the template path globally or by class instance
- setting the extension of the templates globally or by class instance
- blocks (with priority)
- inheritance

Quick example of usage:

    acs_view::$PATH = 'templates/';
    $v = new view();
    $v->load('index');
    $v->title = 'hello';
    echo $v->render();
**Todo**:


- Call subviews
- Create documentation
- More unit tests
- Create examples page
- Make PSR-0 Standard compliant
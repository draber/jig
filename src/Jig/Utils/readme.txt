What is this?
=============
elem is a php class to create all sorts of XML/HTML elements with.

How to use it
=============
There are several possibilities for the usage of the class, the basic call is elem::elementName($args). 

Basic examples:
If the argument is a string, the argument is normally used as content of the element.
- elem::anyElement('content') 
  returns <anyElement>content</anyElement>
  
This also works, when the content is/contains HTML/XML
- elem::anyElement('<elem>foo</elem>') 
  returns <anyElement><elem>foo</elem></anyElement>
  
Some elements don't have actual content but a particular attribute to hold the main information - if the element is known to the class (i.e. any regular HTML element), the content is moved to this attribute.
- elem::script('some/script/src')
  returns <script src="some/script/src"></script>
  
- elem::iframe('some/iframe/src')
  returns <iframe src="some/iframe/src" frameborder="0"></iframe>
  As you can see in the above example, certain default attributes such as frameborder for iframes are automatically added
  
- elem::img('some/img/src')
  returns <img src="some/img/src" alt="" />
  I this example you see that auto closing of the element is done automatically

Basic examples with the <a> element 
- elem::a('some/link/to somthing') 
  returns <a href="some/link/to somthing">some/link/to somthing</a>
  
- elem::a('http://www.example.com/') 
  returns <a href="http://www.example.com/">www.example.com</a> (without http:// in the text)
  
- elem::a('foo@example.com') || elem::a('mailto:foo@example.com')
  returns <a href="encrypted-address-including-mailto">encrypted-address-without-mailto</a>
  
- elem::a('foo@example.com') || elem::a('mailto:foo@example.com')
  returns <a href="encrypted-address-including-mailto">encrypted-address-without-mailto</a>
  
  
Advanced examples with multiple arguments
All functions take up to two arguments, the arrays $attributes and $settings. $attributes is basically analog to the attributes of the HTML/XML element.

Let's create an <input> element for emails (with a useless selected for the sake of example)
$attributes = array(
  'type' => 'email', // default is text
  'value' => 'foo', 
  'class' => 'foo bar',
  'placeholder' => 'do seomething',
  'readonly' => true, // this resolves to 'readonly'
  'selected' => 'selected', // this resolves to 'selected="selected"'
);
- elem::input($attributes) 
  returns <input type="email" value="foo" class="foo bar" readonly selected="selected" />

Normally you would rarely use $settings but if you need to refer the member $settings of the class for details
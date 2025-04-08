![Regular.js](https://i.pickadummy.com/900x100?brightness=2&contrast=-1&size=100&color=000&outline=fff&font=Caviar%20Dreams&text=Regular.js&cache=13)

### *A light and simple JavaScript Library*

[![GitHub license](https://img.shields.io/badge/license-MIT-3490DC)](https://github.com/regularlabs/regularjs/blob/master/LICENCE)
[![PRs welcome](https://img.shields.io/badge/PRs-welcome-F6993F.svg)](https://github.com/regularlabs/regularjs/pulls)
[![coded with passion by Regular Labs](https://img.shields.io/badge/%3C%2F%3E%20with%20%E2%99%A5%20by-Regular%20Labs-E3342F.svg)](https://github.com/regularlabs)

Very small: ![~1.8 kB gzipped](https://img.shields.io/badge/gzipped-~1.8%20kB-38C172.svg) 😮

---

This library contains a number of simple static javascript functions.

[hasClasses](#hasclasses)<br>
[addClasses](#addclasses)<br>
[removeClasses](#removeclasses)<br>
[toggleClasses](#toggleclasses)<br>
[show](#show)<br>
[hide](#hide)<br>
[fadeIn](#fadein)<br>
[fadeOut](#fadeout)<br>
[createElementFromHTML](#createelementfromhtml)<br>
[onReady](#onready)<br>
[loadUrl](#loadurl)<br>
[alias](#alias)<br>
<br>
[Function Aliases](#function-aliases)

---

## hasClasses

Checks if the element contains one or more of the given class names.

#### Syntax

```javascript
Regular.hasClasses(selector, classes, true)
```

| Parameter  | Description                                                                                        | Default |
|------------|----------------------------------------------------------------------------------------------------|:-------:|
| `selector` | A CSS selector string or a HTMLElement object.                                                     |         |
| `classes`  | A string or array of class names.                                                                  |         |
| `matchAll` | Optional boolean whether the element should have all given classes (true) or at least one (false). | `true`  |

##### Returns

`boolean` true or false based on whether the element contains one or more of the given class names.

#### Examples

```html
<div id="mydiv" class="foo bar">...</div>
```

```javascript

Regular.hasClasses('#mydiv', 'foo');
// => true

Regular.hasClasses('#mydiv', 'baz');
// => false

Regular.hasClasses('#mydiv', 'bar baz');
// => false

Regular.hasClasses('#mydiv', ['bar', 'baz']);
// => false

const div = document.querySelector('#mydiv');
Regular.hasClasses(div, ['bar', 'baz'], false);
// => true
```

---

## addClasses

Adds given class name(s) to the element.

#### Syntax

```javascript
Regular.addClasses(selector, classes)
```

| Parameter  | Description                                                                         |
|------------|-------------------------------------------------------------------------------------|
| `selector` | A CSS selector string, a HTMLElement object or a collection of HTMLElement objects. |
| `classes`  | A string or array of class names.                                                   |

#### Examples

```html
<div id="mydiv" class="foo">...</div>
```

```javascript
Regular.addClasses('#mydiv', 'bar');
// => class="foo bar"

Regular.addClasses('#mydiv', 'foo');
// => class="foo"

Regular.addClasses('#mydiv', 'bar baz');
// => class="foo bar baz"

const div = document.querySelector('#mydiv');
Regular.addClasses(div, ['bar', 'baz']);
// => class="foo bar baz"
```

---

## removeClasses

Removes given class name(s) from the element.

#### Syntax

```javascript
Regular.removeClasses(selector, classes)
```

| Parameter  | Description                                                                         |
|------------|-------------------------------------------------------------------------------------|
| `selector` | A CSS selector string, a HTMLElement object or a collection of HTMLElement objects. |
| `classes`  | A string or array of class names.                                                   |

#### Examples

```html
<div id="mydiv" class="foo bar baz">...</div>
```

```javascript
Regular.removeClasses('#mydiv', 'bar');
// => class="foo baz"

Regular.removeClasses('#mydiv', 'bar baz');
// => class="foo"

Regular.removeClasses('#mydiv', ['bar', 'baz']);
// => class="foo"

const div = document.querySelector('#mydiv');
Regular.removeClasses(div, 'qux');
// => class="foo bar baz"
```

---

## toggleClasses

Toggles given class name(s) of the element.

#### Syntax

```javascript
Regular.toggleClasses(selector, classes)
```

| Parameter  | Description                                                                         |
|------------|-------------------------------------------------------------------------------------|
| `selector` | A CSS selector string, a HTMLElement object or a collection of HTMLElement objects. |
| `classes`  | A string or array of class names.                                                   |

#### Examples

```html
<div id="mydiv" class="foo bar baz">...</div>
```

```javascript
Regular.toggleClasses('#mydiv', 'bar');
// => class="foo baz"

Regular.toggleClasses('#mydiv', 'bar baz');
// => class="foo"

Regular.toggleClasses('#mydiv', ['bar', 'qux']);
// => class="foo baz qux"

const div = document.querySelector('#mydiv');
Regular.toggleClasses(div, 'qux');
// => class="foo bar baz qux"
```

---

## show

Shows the given element (changes opacity and display attributes).

#### Syntax

```javascript
Regular.show(el)
```

| Parameter  | Description                                                                         |
|------------|-------------------------------------------------------------------------------------|
| `selector` | A CSS selector string, a HTMLElement object or a collection of HTMLElement objects. |

#### Examples

```html
<div id="mydiv" style="display:none;">...</div>
```

```javascript
Regular.show('#mydiv');

const div = document.querySelector('#mydiv');
Regular.show(div);
```

---

## hide

Hides the given element (changes opacity and display attributes).

#### Syntax

```javascript
Regular.hide(el)
```

| Parameter  | Description                                                                         |
|------------|-------------------------------------------------------------------------------------|
| `selector` | A CSS selector string, a HTMLElement object or a collection of HTMLElement objects. |

#### Examples

```html
<div id="mydiv">...</div>
```

```javascript
Regular.hide(div);

const div = document.querySelector('#mydiv');
Regular.hide('#mydiv');
```

---

## fadeIn

Fades in the the given element.

#### Syntax

```javascript
Regular.fadeIn(selector, duration, oncomplete)
```

| Parameter    | Description                                                                         | Default |
|--------------|-------------------------------------------------------------------------------------|:-------:|
| `selector`   | A CSS selector string, a HTMLElement object or a collection of HTMLElement objects. |         |
| `duration`   | Optional duration of the effect in milliseconds.                                    |  `250`  |
| `oncomplete` | Optional callback function to execute when effect is completed.                     |         |

#### Examples

```html
<div id="mydiv" style="display:none;">...</div>
```

```javascript
Regular.fadeIn('#mydiv');

const div = document.querySelector('#mydiv');
Regular.fadeIn(div, 1000, () => console.log('Faded in'));
```

---

## fadeOut

Fades out the the given element.

#### Syntax

```javascript
Regular.fadeOut(selector, duration, oncomplete)
```

| Parameter    | Description                                                                         | Default |
|--------------|-------------------------------------------------------------------------------------|:-------:|
| `selector`   | A CSS selector string, a HTMLElement object or a collection of HTMLElement objects. |         |
| `duration`   | Optional duration of the effect in milliseconds.                                    |  `250`  |
| `oncomplete` | Optional callback function to execute when effect is completed.                     |         |

#### Examples

```html
<div id="mydiv">...</div>
```

```javascript
Regular.fadeOut('#mydiv');

const div = document.querySelector('#mydiv');
Regular.fadeOut(div, 1000, () => console.log('Faded out'));
```

---

## createElementFromHTML

Converts a string with HTML code to a HTMLElement object.

#### Syntax

```javascript
Regular.createElementFromHTML(html)
```

| Parameter | Description            |
|-----------|------------------------|
| `html`    | String with HTML code. |

##### Returns

`HTMLElement` object based on the given string.

#### Example

```javascript
const mydiv = Regular.createElementFromHTML(`<div id="mydiv" class="foo">My <strong>cool</strong> element!</div>`);
```

---

## onReady

Runs a function when the document is loaded (on ready state).

#### Syntax

```javascript
Regular.onReady(func)
```

| Parameter | Description                                          |
|-----------|------------------------------------------------------|
| `func`    | Callback function to execute when document is ready. |

#### Example

```
Regular.onReady(`console.log('Document is ready!')`);
```

---

## loadUrl

Loads a url with optional POST data and optionally calls a function on success or fail.

#### Syntax

```javascript
Regular.loadUrl(url, data, success, fail)
```

| Parameter | Description                                                                         |
|-----------|-------------------------------------------------------------------------------------|
| `url`     | String containing the url to load.                                                  |
| `data`    | Optional string representing the POST data to send along.                           |
| `success` | Optional callback function to execute when the url loads successfully (status 200). |
| `fail`    | Optional callback function to execute when the url fails to load.                   |

The result (responseText), status and the full XMLHttpRequest object will be passed to the callback functions.

#### Example

```javascript
Regular.loadUrl('my/url.php');

Regular.loadUrl(
    'my/url.php', 
    'id=123&format=ajax', 
    (result) => {
        console.log('Yeah!'); 
        console.log(result); 
    }, 
    (result, status) => console.log(`Oh no! Failed with status: ${status}`)
);
```

---

## alias

Sets a global alias for the Regular class.

#### Syntax

```javascript
Regular.alias(word)
```

| Parameter | Description                                                                |
|-----------|----------------------------------------------------------------------------|
| `word`    | A string (character or word) representing the alias for the Regular class. |

##### Returns

`boolean` true if the alias is created, false if the given alias already exists as a global variable names.

#### Example

```javascript
Regular.alias('$');

$.addClass('#myDiv', 'foo');
```

---

## Function Aliases

There are some aliases for some of the functions:

| Function    | Alias for     |
|-------------|---------------|
| hasClass    | hasClasses    |
| addClass    | addClasses    |
| removeClass | removeClasses |
| toggleClass | toggleClasses |
| as          | alias         |

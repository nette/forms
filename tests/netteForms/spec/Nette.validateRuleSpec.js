describe('Nette.getValue & validateRule', function() {
	let testContainer;

	it('text input', function() {
		testContainer.innerHTML = `<form><input type="text" name="input"></form>`;

		let form = testContainer.querySelector('form'),
			el = form.input;

		expect(Nette.getValue(el)).toBe('');
		expect(Nette.validateRule(el, 'filled')).toBe(false);
		expect(Nette.validateRule(el, 'blank')).toBe(true);
		expect(Nette.validateRule(el, 'equal', '')).toBe(true);
		expect(Nette.validateRule(el, 'static', true)).toBe(true);
		expect(Nette.validateRule(el, 'static', false)).toBe(false);

		el.value = ' hello ';
		expect(Nette.getValue(el)).toBe('hello');
		expect(Nette.validateRule(el, 'filled')).toBe(true);
		expect(Nette.validateRule(el, 'blank')).toBe(false);
		expect(Nette.validateRule(el, 'equal', '')).toBe(false);
		expect(Nette.validateRule(el, 'equal', 'hello')).toBe(true);
		expect(Nette.validateRule(el, 'equal', ['a', 'b'])).toBe(false);
		expect(Nette.validateRule(el, 'equal', ['a', 'hello', 'b'])).toBe(true);
		expect(Nette.validateRule(el, 'notEqual', 'hello')).toBe(false);
		expect(Nette.validateRule(el, 'minLength', 1)).toBe(true);
		expect(Nette.validateRule(el, 'minLength', 6)).toBe(false);
		expect(Nette.validateRule(el, 'maxLength', 1)).toBe(false);
		expect(Nette.validateRule(el, 'maxLength', 6)).toBe(true);
		expect(Nette.validateRule(el, 'length', 1)).toBe(false);
		expect(Nette.validateRule(el, 'length', 5)).toBe(true);
		expect(Nette.validateRule(el, 'length', 6)).toBe(false);
		expect(Nette.validateRule(el, 'length', [1, 6])).toBe(true);
		expect(Nette.validateRule(el, 'length', [3, 4])).toBe(false);
		expect(Nette.validateRule(el, 'email')).toBe(false);
		expect(Nette.validateRule(el, 'url')).toBe(true);
		expect(Nette.validateRule(el, 'regexp', '/\\d+/')).toBe(false);
		expect(Nette.validateRule(el, 'regexp', '/\\w+/')).toBe(true);
		expect(Nette.validateRule(el, 'pattern', '\\d+')).toBe(false);
		expect(Nette.validateRule(el, 'pattern', '\\d')).toBe(false);
		expect(Nette.validateRule(el, 'pattern', '\\w')).toBe(false);
		expect(Nette.validateRule(el, 'pattern', '\\w+')).toBe(true);
		expect(Nette.validateRule(el, 'pattern', 'hello')).toBe(true);
		expect(Nette.validateRule(el, 'pattern', 'HELLO')).toBe(false);
		expect(Nette.validateRule(el, 'patternCaseInsensitive', '\\d+')).toBe(false);
		expect(Nette.validateRule(el, 'patternCaseInsensitive', '\\d')).toBe(false);
		expect(Nette.validateRule(el, 'patternCaseInsensitive', '\\w')).toBe(false);
		expect(Nette.validateRule(el, 'patternCaseInsensitive', '\\w+')).toBe(true);
		expect(Nette.validateRule(el, 'patternCaseInsensitive', 'hello')).toBe(true);
		expect(Nette.validateRule(el, 'patternCaseInsensitive', 'HELLO')).toBe(true);
		expect(Nette.validateRule(el, 'integer')).toBe(false);
		expect(Nette.validateRule(el, 'float')).toBe(false);

		el.value = 'john@doe.com';
		expect(Nette.validateRule(el, 'email')).toBe(true);

		el.value = 'nette.org';
		expect(Nette.validateRule(el, 'url')).toBe(true);

		el.value = '-1234';
		expect(Nette.validateRule(el, 'integer')).toBe(true);
		expect(Nette.validateRule(el, 'float')).toBe(true);
		expect(Nette.validateRule(el, 'min', -2000)).toBe(true);
		expect(Nette.validateRule(el, 'min', -1000)).toBe(false);
		expect(Nette.validateRule(el, 'max', -2000)).toBe(false);
		expect(Nette.validateRule(el, 'max', -1000)).toBe(true);
		expect(Nette.validateRule(el, 'range', ['-2000', '-1000'])).toBe(true);
		expect(Nette.validateRule(el, 'range', [10, null])).toBe(false);

		el.value = '-12.5';
		expect(Nette.validateRule(el, 'integer')).toBe(false);
		expect(Nette.validateRule(el, 'float')).toBe(true);
		expect(Nette.validateRule(el, 'min', -2000)).toBe(true);
		expect(Nette.validateRule(el, 'min', -10)).toBe(false);
		expect(Nette.validateRule(el, 'max', -2000)).toBe(false);
		expect(Nette.validateRule(el, 'max', -10)).toBe(true);
		expect(Nette.validateRule(el, 'range', ['-12.6', '-12.4'])).toBe(true);
		expect(Nette.validateRule(el, 'range', [-5, 10])).toBe(false);
	});


	it('text area', function() {
		testContainer.innerHTML = `<form><textarea name="input"></textarea></form>`;

		let form = testContainer.querySelector('form'),
			el = form.input;

		expect(Nette.getValue(el)).toBe('');
		expect(Nette.validateRule(el, 'filled')).toBe(false);
		expect(Nette.validateRule(el, 'blank')).toBe(true);
		expect(Nette.validateRule(el, 'equal', '')).toBe(true);

		el.value = ' hello ';
		expect(Nette.getValue(el)).toBe(' hello ');
		expect(Nette.validateRule(el, 'filled')).toBe(true);
		expect(Nette.validateRule(el, 'blank')).toBe(false);
		expect(Nette.validateRule(el, 'equal', 'hello')).toBe(false);
		expect(Nette.validateRule(el, 'equal', ' hello ')).toBe(true);
	});


	it('upload', function() {
		testContainer.innerHTML = `<form method="post" enctype="multipart/form-data"><input type="file" name="input"></form>`;

		let form = testContainer.querySelector('form'),
			el = form.input;

		expect(Nette.getValue(el) instanceof FileList).toBe(true);
		expect(Nette.getValue(el).length).toBe(0);
	});


	it('multi upload', function() {
		testContainer.innerHTML = `<form method="post" enctype="multipart/form-data"><input type="file" name="input[]" multiple></form>`;

		let form = testContainer.querySelector('form'),
			el = form['input[]'];

		expect(Nette.getValue(el) instanceof FileList).toBe(true);
		expect(Nette.getValue(el).length).toBe(0);
	});


	it('checkbox', function() {
		testContainer.innerHTML = `<form><input type="checkbox" name="input" value="r"></form>`;

		let form = testContainer.querySelector('form'),
			el = form.input;

		expect(Nette.getValue(el)).toBe(false);
		expect(Nette.validateRule(el, 'filled')).toBe(false);
		expect(Nette.validateRule(el, 'blank')).toBe(true);
		expect(Nette.validateRule(el, 'equal', false)).toBe(true);

		el.checked = true;
		expect(Nette.getValue(el)).toBe(true);
		expect(Nette.validateRule(el, 'filled')).toBe(true);
		expect(Nette.validateRule(el, 'blank')).toBe(false);
		expect(Nette.validateRule(el, 'equal', true)).toBe(true);
	});


	it('checkbox list', function() {
		testContainer.innerHTML = `<form>
			<input type="checkbox" name="input[]" value="r" id="input-r">
			<input type="checkbox" name="input[]" value="g" id="input-g">
			<input type="checkbox" name="input[]" value="b" id="input-b">
		</form>`;

		let form = testContainer.querySelector('form'),
			el = form['input[]'];

		expect(Nette.getValue(el)).toEqual([]);
		expect(Nette.validateRule(el, 'filled')).toBe(false);
		expect(Nette.validateRule(el, 'blank')).toBe(true);
		expect(Nette.validateRule(el, 'equal', ['r', 'g', 'b'])).toBe(true);

		testContainer.querySelector('#input-r').checked = true;
		expect(Nette.getValue(el)).toEqual(['r']);
		expect(Nette.validateRule(el, 'filled')).toBe(true);
		expect(Nette.validateRule(el, 'blank')).toBe(false);
		expect(Nette.validateRule(el, 'equal', 'r')).toBe(true);
		expect(Nette.validateRule(el, 'equal', 'g')).toBe(false);
		expect(Nette.validateRule(el, 'equal', ['r', 'g'])).toBe(true);
		expect(Nette.validateRule(el, 'minLength', 1)).toBe(true);
		expect(Nette.validateRule(el, 'minLength', 2)).toBe(false);

		testContainer.querySelector('#input-g').checked = true;
		expect(Nette.getValue(el)).toEqual(['r', 'g']);
		expect(Nette.validateRule(el, 'filled')).toBe(true);
		expect(Nette.validateRule(el, 'blank')).toBe(false);
		expect(Nette.validateRule(el, 'equal', 'r')).toBe(false);
		expect(Nette.validateRule(el, 'equal', ['r', 'x'])).toBe(false);
		expect(Nette.validateRule(el, 'equal', ['r', 'g'])).toBe(true);
		expect(Nette.validateRule(el, 'equal', ['r', 'g', 'b'])).toBe(true);
		expect(Nette.validateRule(el, 'minLength', 2)).toBe(true);
		expect(Nette.validateRule(el, 'minLength', 3)).toBe(false);
	});


	it('checkbox list with single item', function() {
		testContainer.innerHTML = `<form><input type="checkbox" name="input[]" value="r" id="input-r"></form>`;

		let form = testContainer.querySelector('form'),
			el = form['input[]'];

		expect(Nette.getValue(el)).toEqual([]);
		expect(Nette.validateRule(el, 'filled')).toBe(false);
		expect(Nette.validateRule(el, 'blank')).toBe(true);
		expect(Nette.validateRule(el, 'equal', ['r', 'g', 'b'])).toBe(true);

		testContainer.querySelector('#input-r').checked = true;
		expect(Nette.getValue(el)).toEqual(['r']);
		expect(Nette.validateRule(el, 'filled')).toBe(true);
		expect(Nette.validateRule(el, 'blank')).toBe(false);
		expect(Nette.validateRule(el, 'equal', 'r')).toBe(true);
		expect(Nette.validateRule(el, 'equal', 'g')).toBe(false);
		expect(Nette.validateRule(el, 'equal', ['r', 'g'])).toBe(true);
		expect(Nette.validateRule(el, 'minLength', 1)).toBe(true);
		expect(Nette.validateRule(el, 'minLength', 2)).toBe(false);
	});


	it('radio', function() {
		testContainer.innerHTML = `<form><input type="radio" name="input" value="f"><form>`;

		let form = testContainer.querySelector('form'),
			el = form.input;

		expect(Nette.getValue(el)).toBe(null);
		expect(Nette.validateRule(el, 'filled')).toBe(false);
		expect(Nette.validateRule(el, 'blank')).toBe(true);
		expect(Nette.validateRule(el, 'equal', ['f', 'm'])).toBe(false);

		el.checked = true;
		expect(Nette.getValue(el)).toBe('f');
		expect(Nette.validateRule(el, 'filled')).toBe(true);
		expect(Nette.validateRule(el, 'blank')).toBe(false);
		expect(Nette.validateRule(el, 'equal', 'f')).toBe(true);
		expect(Nette.validateRule(el, 'equal', 'm')).toBe(false);
		expect(Nette.validateRule(el, 'equal', ['f', 'm'])).toBe(true);
	});


	it('radio list', function() {
		testContainer.innerHTML = `<form>
			<input type="radio" name="input" value="m" id="input-m">
			<input type="radio" name="input" value="f" id="input-f">
		</form>`;

		let form = testContainer.querySelector('form'),
			el = form.input;

		expect(Nette.getValue(el)).toBe(null);
		expect(Nette.validateRule(el, 'filled')).toBe(false);
		expect(Nette.validateRule(el, 'blank')).toBe(true);
		expect(Nette.validateRule(el, 'equal', ['f', 'm'])).toBe(false);

		testContainer.querySelector('#input-m').checked = true;
		expect(Nette.getValue(el)).toBe('m');

		testContainer.querySelector('#input-f').checked = true;
		expect(Nette.getValue(el)).toBe('f');
		expect(Nette.validateRule(el, 'filled')).toBe(true);
		expect(Nette.validateRule(el, 'blank')).toBe(false);
		expect(Nette.validateRule(el, 'equal', 'f')).toBe(true);
		expect(Nette.validateRule(el, 'equal', 'm')).toBe(false);
		expect(Nette.validateRule(el, 'equal', ['f', 'm'])).toBe(true);
	});


	it('selectbox', function() {
		testContainer.innerHTML = `<form>
			<select name="input">
				<option value="">Prompt</option>
				<optgroup label="World"><option value="bu" id="option-2">Buranda</option></optgroup>
				<option value="?" id="option-3">other</option>
			</select>
		</form>`;

		let form = testContainer.querySelector('form'),
			el = form.input;

		expect(Nette.getValue(el)).toBe('');
		expect(Nette.validateRule(el, 'filled')).toBe(false);
		expect(Nette.validateRule(el, 'blank')).toBe(true);

		testContainer.querySelector('#option-2').selected = true;
		expect(Nette.getValue(el)).toBe('bu');
		expect(Nette.validateRule(el, 'filled')).toBe(true);
		expect(Nette.validateRule(el, 'blank')).toBe(false);
		expect(Nette.validateRule(el, 'equal', 'bu')).toBe(true);
		expect(Nette.validateRule(el, 'equal', 'x')).toBe(false);
		expect(Nette.validateRule(el, 'equal', ['bu', 'x'])).toBe(true);

		testContainer.querySelector('#option-3').selected = true;
		expect(Nette.getValue(el)).toBe('?');
	});


	it('multi selectbox', function() {
		testContainer.innerHTML = `<form>
			<select name="input[]" multiple>
				<optgroup label="World"><option value="bu" id="option-2">Buranda</option></optgroup>
				<option value="?" id="option-3">other</option>
			</select>
		</form>`;

		let form = testContainer.querySelector('form'),
			el = form['input[]'];

		expect(Nette.getValue(el)).toEqual([]);
		expect(Nette.validateRule(el, 'filled')).toBe(false);
		expect(Nette.validateRule(el, 'blank')).toBe(true);

		testContainer.querySelector('#option-2').selected = true;
		expect(Nette.getValue(el)).toEqual(['bu']);
		expect(Nette.validateRule(el, 'filled')).toBe(true);
		expect(Nette.validateRule(el, 'blank')).toBe(false);
		expect(Nette.validateRule(el, 'equal', 'bu')).toBe(true);
		expect(Nette.validateRule(el, 'equal', 'x')).toBe(false);
		expect(Nette.validateRule(el, 'equal', ['bu', 'x'])).toBe(true);
		expect(Nette.validateRule(el, 'minLength', 1)).toBe(true);
		expect(Nette.validateRule(el, 'minLength', 2)).toBe(false);

		testContainer.querySelector('#option-3').selected = true;
		expect(Nette.getValue(el)).toEqual(['bu', '?']);
		expect(Nette.validateRule(el, 'filled')).toBe(true);
		expect(Nette.validateRule(el, 'blank')).toBe(false);
		expect(Nette.validateRule(el, 'equal', 'bu')).toBe(false);
		expect(Nette.validateRule(el, 'equal', ['bu', 'x'])).toBe(false);
		expect(Nette.validateRule(el, 'equal', ['bu', '?'])).toBe(true);
		expect(Nette.validateRule(el, 'equal', ['bu', '?', 'x'])).toBe(true);
		expect(Nette.validateRule(el, 'minLength', 2)).toBe(true);
		expect(Nette.validateRule(el, 'minLength', 3)).toBe(false);
	});


	it('missing name', function() {
		testContainer.innerHTML = '<form><input></form>';

		let form = testContainer.querySelector('form'),
			el = form.elements[0];

		expect(Nette.getValue(el)).toEqual('');
		el.value = ' hello ';
		expect(Nette.getValue(el)).toBe('hello');
	});


	beforeEach(function() {
		testContainer = document.createElement('div');
		document.body.appendChild(testContainer);
	});

	afterEach(function() {
		document.body.removeChild(testContainer);
	});
});

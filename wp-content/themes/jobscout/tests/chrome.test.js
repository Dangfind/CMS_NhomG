/* Isolated interaction checks. Run: node tests/chrome.test.js */
'use strict';
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');

function element(attributes = {}) {
    return {
        attributes, hidden: false, listeners: {},
        setAttribute(key, value) { this.attributes[key] = value; },
        getAttribute(key) { return this.attributes[key]; },
        addEventListener(name, handler) { this.listeners[name] = handler; },
        focus() { this.focused = true; }
    };
}

for (const width of [1440, 1024, 768, 375]) {
    const toggle = element({ 'aria-expanded': 'false' });
    const navigation = element();
    navigation.contains = target => target === navigation;
    const header = element();
    header.querySelector = selector => selector.includes('toggle') ? toggle : navigation;
    header.contains = target => [toggle, header, navigation].includes(target);
    const media = element();
    media.matches = width <= 1024;
    const status = element({ 'data-unavailable': 'Email has not been saved or sent.' });
    const email = element();
    const submit = element();
    submit.disabled = true;
    const form = element();
    let valid = true;
    form.reportValidity = () => valid;
    form.querySelector = selector => selector.includes('status') ? status : selector.includes('input') ? email : submit;
    const document = element();
    document.querySelector = () => header;
    document.querySelectorAll = () => [form];
    vm.runInNewContext(fs.readFileSync(require('node:path').join(__dirname, '../js/cmsng-chrome.js'), 'utf8'), {
        document, window: { matchMedia: () => media }
    });
    assert.equal(toggle.hidden, width > 1024);
    assert.equal(navigation.hidden, width <= 1024);
    if (width <= 1024) {
        toggle.listeners.click();
        assert.equal(navigation.hidden, false);
        assert.equal(toggle.getAttribute('aria-expanded'), 'true');
        header.listeners.keydown({ key: 'Escape' });
        assert.equal(navigation.hidden, true);
        assert.equal(toggle.focused, true);
        toggle.listeners.click();
        document.listeners.click({ target: {} });
        assert.equal(navigation.hidden, true);
        toggle.listeners.click();
        media.matches = false;
        media.listeners.change();
        assert.equal(navigation.hidden, false);
        assert.equal(toggle.hidden, true);
        media.matches = true;
        media.listeners.change();
        assert.equal(navigation.hidden, true);
        assert.equal(toggle.getAttribute('aria-expanded'), 'false');
    }
    assert.equal(submit.disabled, false);
    status.hidden = true;
    let prevented = false;
    valid = false;
    form.listeners.submit({ preventDefault() { prevented = true; } });
    assert.equal(prevented, true);
    assert.equal(status.hidden, true);
    valid = true;
    form.listeners.submit({ preventDefault() {} });
    assert.equal(status.hidden, false);
    assert.match(status.textContent, /not been saved or sent/);
    email.listeners.input();
    assert.equal(status.hidden, true);
    console.log(`PASS: navigation breakpoint and newsletter behavior at ${width}px (isolated DOM, not visual layout)`);
}

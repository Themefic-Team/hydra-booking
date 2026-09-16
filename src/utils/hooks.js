// src/utils/hooks.js
const filters = {};
const actions = {};

export function addFilter(hookName, callback, priority = 10) {
    if (!filters[hookName]) {
        filters[hookName] = [];
    }
    filters[hookName].push({ callback, priority });
    filters[hookName].sort((a, b) => a.priority - b.priority);
}

export function applyFilters(hookName, value, ...args) {
    if (!filters[hookName]) {
        return value;
    }
    let result = value;
    for (const filter of filters[hookName]) {
        result = filter.callback(result, ...args);
    }
    return result;
}

export function addAction(hookName, callback, priority = 10) {
    if (!actions[hookName]) {
        actions[hookName] = [];
    }
    actions[hookName].push({ callback, priority });
    actions[hookName].sort((a, b) => a.priority - b.priority);
}

export function doAction(hookName, ...args) {
    if (!actions[hookName]) {
        return;
    }
    for (const action of actions[hookName]) {
        action.callback(...args);
    }
}

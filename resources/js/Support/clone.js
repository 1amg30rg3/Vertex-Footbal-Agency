export function cloneForForm(value) {
    return JSON.parse(JSON.stringify(value ?? {}));
}

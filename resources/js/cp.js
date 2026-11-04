const wand = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="m3 21 12-12"/><path d="m13 7 4 4"/><path d="M18 2v4"/><path d="M16 4h4"/><path d="M6 3v2"/><path d="M5 4h2"/><path d="M20 15v2"/><path d="M19 16h2"/></svg>';

// The asset editor's publish container is named "asset" and carries the id as
// its reference, a field action gets neither, so it looks them up from the field.
const assetId = (vm) => {
    for (let component = vm; component; component = component.$parent) {
        if (component.$props?.name === 'asset' && component.$props?.reference) {
            return component.$props.reference;
        }
    }
};

Statamic.booting(() => {
    Statamic.$fieldActions.add('textarea-fieldtype', {
        title: __('Generate Alt Text'),
        icon: wand,
        quick: true,
        visible: ({ handle, vm }) => handle === 'alt' && !!assetId(vm),
        run: ({ vm, update }) => vm.$axios
            .post(cp_url('statamic-ai-alt/generate'), { id: assetId(vm) })
            .then(({ data }) => update(data.text))
            .catch(() => vm.$toast.error(__('Alt text could not be generated'))),
    });
});

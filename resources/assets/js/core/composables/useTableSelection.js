import { ref, computed } from 'vue'

export function useTableSelection(dtRef) {
    const selected = ref([])

    const allSelected = computed(() => {
        const data = dtRef.value?.tableData ?? []
        return data.length > 0 && data.every(row => selected.value.includes(row.id))
    })

    function toggleRow(id) {
        const idx = selected.value.indexOf(id)
        if (idx === -1) selected.value.push(id)
        else selected.value.splice(idx, 1)
    }

    function toggleAll(e) {
        const data = dtRef.value?.tableData ?? []
        const ids = new Set(data.map(r => r.id))
        if (e.target.checked)
            selected.value.push(...data.map(r => r.id).filter(id => !selected.value.includes(id)))
        else
            selected.value = selected.value.filter(id => !ids.has(id))
    }

    return { selected, allSelected, toggleRow, toggleAll }
}

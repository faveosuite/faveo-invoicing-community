import { ref, computed, watch } from 'vue'

export function useTableSelection(dtRef) {
    const selected = ref([])

    // Keep only rows still on screen whenever the table reloads (page, search, filter, after a
    // delete) — otherwise a bulk action's "N selected" also counts rows the admin can't see.
    watch(() => dtRef.value?.tableData, (data) => {
        const ids = new Set((data ?? []).map(r => r.id))
        const kept = selected.value.filter(id => ids.has(id))
        if (kept.length !== selected.value.length) selected.value = kept
    })

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

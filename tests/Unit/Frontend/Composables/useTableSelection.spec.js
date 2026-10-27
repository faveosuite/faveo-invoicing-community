import { ref, nextTick } from 'vue'
import { useTableSelection } from '@/core/composables/useTableSelection'

describe('useTableSelection', () => {
    it('drops selected ids that are no longer on screen when the table reloads', async () => {
        const dtRef = ref({ tableData: [{ id: 1 }, { id: 2 }, { id: 3 }] })
        const { selected, toggleRow } = useTableSelection(dtRef)
        toggleRow(1)
        toggleRow(2)

        // e.g. row 2 was deleted, or the admin moved to another page
        dtRef.value = { tableData: [{ id: 1 }, { id: 3 }] }
        await nextTick()

        expect(selected.value).toEqual([1]) // the bulk-delete count must match what is ticked
    })
})

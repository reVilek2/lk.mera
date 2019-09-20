const DocumentItem = {
    props: {
        item: {
            type: Object,
            default: () => {}
        },
        columns: {
            type: Array,
            default: []
        }
    },
    methods: {
        updateDocument(document) {
            this.$emit('updateDocument', document);
        },
        deleteDocument() {
           this.$emit('deleteDocument');
        },
        isColumnExist(name) {
            let column = this.columns.find(column => {
                return column.name == name
            })

            if(column){
                return true;
            }
            return false;
        }
    }
}

export default DocumentItem;

<template>
    <tr>
        <td v-if="isColumnExist('created_at')" class="created_at">
            {{item.created_at_humanize}}
        </td>
        <td v-if="isColumnExist('client_full_name')" class="client_full_name">
            <span v-if="item.client.last_name">{{item.client.last_name}} </span><span v-if="item.client.first_name">{{item.client.first_name}} </span><span v-if="item.client.second_name">{{item.client.second_name}}</span>
        </td>
        <td v-if="isColumnExist('name')" class="name">
            {{item.name}}
        </td>
        <td v-if="isColumnExist('file')" class="file">
            <div v-if="item.files.length > 0" v-for="file in item.files" :key="file.id" class="btn-group" >
                <a class="document-link dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                    <i class="fa fa-file-pdf-o" v-show="file.type==='application/pdf'"></i>
                    {{file.origin_name}}
                </a>
                <ul class="dropdown-menu" role="menu">
                    <li><a :href="'/reports/'+item.id+'/files/'+file.id" target="_blank">Посмотреть</a></li>
                    <li><a :href="'/reports/'+item.id+'/files/'+file.id+'?download=1'">Скачать</a></li>
                </ul>
            </div>
        </td>
        <td v-if="isColumnExist('status')" class="status">
            <document-status :signed="item.signed"
                                :paid="item.paid"
                                :item="item"
                                :key="item.id"
                                @updateDocument="updateDocument"></document-status>
        </td>
        <td v-if="isColumnExist('amount')" class="amount">
            {{item.amount_humanize}}
        </td>
        <td v-if="isColumnExist('manager_full_name')" class="manager_full_name">
            <span v-if="item.manager.last_name">{{item.manager.last_name}} </span><span v-if="item.manager.first_name">{{item.manager.first_name}} </span><span v-if="item.manager.second_name">{{item.manager.second_name}}</span>
        </td>
        <td v-if="isColumnExist('action')" class="action">
            <document-action :signed="item.signed"
                                :paid="item.paid"
                                :item="item"
                                :key="'0'+item.id"
                                :key-id="'0'+item.id"
                                @updateDocument="updateDocument"
                                @deleteDocument="deleteDocument"
            >
            </document-action>
        </td>
    </tr>
</template>
<script>
    import DocumentStatus from './DocumentStatus';
    import DocumentAction from './DocumentAction';
    import DocumentItem from '../../mixins/DocumentItem';

    export default {
        mixins: [DocumentItem],
        components: { documentStatus: DocumentStatus, documentAction: DocumentAction},
    };
</script>

<template>
    <lit-base-component
        v-bind="$attrs"
        :component="wrapper"
        style="position: relative;"
        @click="runOrShowModal"
    >
        <b-spinner
            v-if="sendingEventRequest"
            small
            type="grow"
            style="
                position: absolute;
                left: 0;
                top: 0;
                right: 0;
                bottom: 0;
                margin: auto;
            "
        />
        <lit-base-component
            v-if="modal"
            v-bind="$attrs"
            :component="modal"
            :id="modalId"
            @ok="runAction"
        >
            <span>{{ modal.props.message }}</span>
            <div class="mt-2 row" v-if="modal.form">
                <lit-field
                    v-for="(field, key) in modal.form.fields"
                    :key="key"
                    :field="field"
                    :model-id="0"
                    :model="attributes"
                    :save="false"
                />
            </div>
        </lit-base-component>
    </lit-base-component>
</template>

<script>
export default {
    name: 'Action',
    props: {
        wrapper: {
            type: Object,
            required: true,
        },
        modal: {
            type: Object,
        },
        sendingEventRequest: {
            type: Boolean,
            default: false,
        },
    },
    data() {
        return {
            modalId: Lit.uuidv4(),
            show: true,
            attributes: this.crud({
                attributes: {},
                translatable: true,
                cast: true,
            }),
        };
    },
    beforeMount() {
        Lit.bus.$on('eventHandled', ({ event, response }) => {
            if (!response.isAxiosError) {
                this.$bvModal.hide(this.modalId);
            }
        });
    },
    methods: {
        /**
         * Run.
         */
        runOrShowModal() {
            if (this.modal) {
                this.$bvModal.show(this.modalId);
            } else {
                this.runAction();
            }
        },

        async runAction(e) {
            if (e) {
                e.preventDefault();
            }

            this.$emit('run', { attributes: this.attributes.attributes });
        },
    },
};
</script>

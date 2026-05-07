<template>
    <img
        :id="id"
        :src="getSrc"
        :class="classes"
        :alt="alternativeText"
        :width="imgWidth"
        :height="imgHeight"
        :style="styleObject"
        @error="onImageLoadError($event)"
    />
</template>

<script>
export default {

    name: 'image-element',

    description: 'Common element for image tag',

    props: {

        /**
         * Unique id of the image
         */
        id: {
            type: String,
            required: true
        },

        /**
         * src atrribute value
         */
        sourceUrl: {
            type: String,
            required: true
        },

        /**
         * Used to style the image
         */
        styleObject: {
            type: Object,
            default: () => {
            }
        },

        /**
         * Class names (bootstrap/admineLTE)
         */
        classes: {
            type: Array,
            default: () => []
        },

        /**alt attribute text */
        alternativeText: {
            type: String,
            default: ''
        },

        /**Image width */
        imgWidth: {
            type: [Number, String],
            default: 'auto'
        },

        /** Image height */
        imgHeight: {
            type: [Number, String],
            default: 'auto'
        },

        /**
         * file name of the image
         * Used when onError event fired
         * ----OR----
         * `sourceUrl` is not truthy
         *
         * NOTE: This file must be present in /themes/default/common/images directory
         */
        defaultImage: {
            type: String,
            default: 'default.png'
        }
    },

    data: () => {
        return {}
    },

    computed: {
        getSrc: function () {
            return this.sourceUrl ? this.sourceUrl : this.basePath() + '/themes/default/img/' + this.defaultImage;
        }
    },


    methods: {
        /**
         * we will need `onerror` hook else without internet it will show a broken image
         **/
        onImageLoadError(event) {
            event.target.onerror = null;
            event.target.src = this.basePath() + '/themes/default/img/' + this.defaultImage;
        }
    }

};
</script>

<style scoped>
img {
    object-fit: contain;
}

.img-circle {
    border-radius: 50%;
}

.profile-user-img {
    border: 3px solid #adb5bd;
    margin: 0 auto;
    padding: 3px;
    width: 100px;
}

.img-rounder{
    border-radius: 50% !important;
}

.img-click {
    width: 100px !important;
    height: 100px !important;
}
</style>

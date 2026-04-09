<template>
    <form-field-template :label="label" :labelStyle="labelStyle" :name="name" :classname="classname" :hint="hint"
                         :required="required">

        <input ref="fileInput" type="file" @change="onFileSelected" style="display:none" multiple :disabled="is_default">

        <div>

            <image-element id="profile-pic" :classes="['profile-user-img', 'img-responsive', 'img-rounder', 'img-click']" :sourceUrl="value" :title="lang(tooltip)" :style-object="styleObj"></image-element>

            <h6 class="text-center font-weight-normal mt-2" :style="labelCss">{{label}}</h6>
        </div>

        <div class="text-center">

            <button class="btn btn-primary btn-sm text-center" :disabled="is_default" data-toggle="modal"
                    @click="$refs.fileInput.click()">
                <i v-if="!buttonName" class="fas fa-exchange-alt"></i>
                {{buttonName ? lang(buttonName) : btnName ? lang('change_icon') : lang('change_logo') }}
            </button>
        </div>

        <modal v-if="showModal" :showModal="showModal" :onClose="onClose" :containerStyle="containerStyle">

            <template #title>

                <div>

                    <h4>Crop Profile</h4>
                </div>
            </template>

            <template #fields>
                <div class="">

                    <vue-cropper ref='cropper' :guides="true" :view-mode="2"
                                 drag-mode="crop"
                                 :auto-crop="true"
                                 :auto-crop-area="100"
                                 :min-container-width="570"
                                 :min-container-height="300"
                                 :background="true"
                                 :rotatable="true"
                                 :src="imageSrc"
                                 :img-style="crop"
                                 @crop="cropImage"
                                 :aspect-ratio="''"
                    >
                    </vue-cropper>

                </div>
                <div class="rotate-button">
                    <button v-if="imageSrc" @click="rotateImage" id="rotate" >
                        <i class="fas fa-sync-alt"></i>&nbsp;{{trans('rotate')}}</button>
                    <button v-if="imageSrc" @click="changeRatio(0)" id="ratio" :class="{ 'active': aspectRatio === 0 }" class="m-1">
                        &nbsp;{{trans('no_ratio')}}</button>
                    <button v-if="imageSrc" @click="changeRatio(1)" id="ratio" :class="{ 'active': aspectRatio === 1 }" class="m-1">
                        &nbsp;{{'1:1 '+trans('ratio')}}</button>
                    <button v-if="imageSrc" @click="changeRatio(2)" id="ratio" :class="{ 'active': aspectRatio === 2 }" class="m-1">
                        &nbsp;{{'16:9 '+trans('ratio')}}</button>
                </div>
            </template>

            <template #controls>

                <button type="button" @click="onSubmit" class="btn btn-primary mt-2 float-start" id="crop_action"><i class="fa fa-check"></i> {{lang('proceed')}}</button>
            </template>
        </modal>
    </form-field-template>
</template>

<script type="text/javascript">

import FormFieldTemplate from "./FormFieldTemplate.vue";
import ImageElement from "../ImageElement.vue";
import Modal from "../Modal.vue";
import VueCropper from "vue-cropperjs";
import tooltip from "../Tooltip.vue";
import {lang} from "../../../helpers/extraLogics";

export default {

    name: "file-upload",

    description: "file upload component along with error block",

    props: {

        label: { type: String, required: true },

        hint: { type: String, default: "" }, //for tooltip message

        name: { type: String, required: true },

        onChange: { type: Function, Required: true },

        classname: { type: String, default: "" },

        required: { type: Boolean, default: false },

        labelStyle:{type:Object},

        id : {type: [String,Number], default:'text-field'},

        value : { type: [Object,String] , default : '' },

        componentName: {type: String, required: true},

        is_default : { type :  [Boolean,Number], default : false },

        btnName : { type : String, default : ''},

        buttonName : { type : String, default : ''},

        labelCss : { type : Object, default : ()=>{}}

    },

    data() {

        return {

            selectedFile:this.value,

            imageSrc:'',

            tooltip : '',

            styleObj : { background : 'none' },

            showModal : false,

            containerStyle:{ width:'800px' },

            crop: { width: '400px', height: '300px' },

            cropImg: '',

            resultImage : '',

            aspectRatio : 0
        };
    },

    beforeMount(){

        this.selectedFile = this.value;

        this.tooltipValue(this.selectedFile);
    },

    watch: {
        value(newVal) {
            this.selectedFile = newVal;
        },
    },

    methods:{
        lang,

        onFileSelected(event) {

            if (!event.target.files[0].type.includes('image/')) {

                this.showCustomAlert(this.lang('select_image'))

                return;
            }

            if(event.target.files[0].type.includes('image/png') || event.target.files[0].type.includes('image/jpg') || event.target.files[0].type.includes('image/jpeg')) {

                if(event.target.files[0].size < 2097152) {

                    this.showModal = true;

                    var element=this.$refs.fileInput;

                    this.selectedFile = event.target.files[0];

                    var input = event.target;

                    if (input.files && input.files[0]) {

                        var reader = new FileReader();

                        reader.onload = (e) => {

                            this.imageSrc = e.target.result;
                            this.$refs.cropper.replace(e.target.result);
                        }
                        reader.readAsDataURL(input.files[0]);
                    }

                    element.value="";

                } else {
                    this.showCustomAlert(this.lang('max_size_new_added'))
                }
            } else {

                this.showCustomAlert(this.lang('restricted_image_file'))
            }

        },

        cropImage() {

            if (this.$refs.cropper) {
                // Call getCroppedCanvas() method
                this.cropImg = this.$refs.cropper.getCroppedCanvas().toDataURL();
            }
        },

        rotateImage() {
            this.$refs.cropper.rotate(90);
        },

        onClose(){

            this.showModal = false;
        },

        onSubmit(){

            var ImageURL = this.cropImg;

            // Split the base64 string in data and contentType
            var block = ImageURL.split(";");

            // Get the content type
            var contentType = block[0].split(":")[1];// In this case "image/gif"

            // get the real base64 content of the file
            var realData = block[1].split(",")[1];// In this case "iVBORw0KGg...."

            // Convert to blob
            this.resultImage = this.b64toBlob(realData,contentType);

            const data = {};

            data['name'] = this.selectedFile.name;

            data['image'] = this.cropImg;

            data['file'] = this.resultImage;

            this.onChange(data,this.name);

            this.onClose();
        },

        b64toBlob(b64Data, filename,contentType = '', sliceSize = 512) {

            var byteCharacters = atob(b64Data);

            var byteArrays = [];

            for (var offset = 0; offset < byteCharacters.length; offset += sliceSize) {

                var slice = byteCharacters.slice(offset, offset + sliceSize);

                var byteNumbers = new Array(slice.length);

                for (var i = 0; i < slice.length; i++) {

                    byteNumbers[i] = slice.charCodeAt(i);
                }

                var byteArray = new Uint8Array(byteNumbers);

                byteArrays.push(byteArray);
            }

            var blob = new Blob(byteArrays, {type: contentType});

            return blob;
        },

        tooltipValue(selectedFile){

            this.tooltip = selectedFile !== null && selectedFile.name === undefined ? selectedFile.split('/')[selectedFile.split('/').length-1] : selectedFile ? selectedFile.name : 'no_file'

            this.styleObj.background = this.tooltip === 'logo.png' ? 'black' : 'none'
        },

        showCustomAlert(message) {
            this.$store.dispatch('setAlert', {
                type: 'danger',
                message: message,
                component_name: this.componentName
            });

        },

        changeRatio(value){
            this.$refs.cropper.setAspectRatio(value);
            this.aspectRatio = value
        }
    },


    components: {
        VueCropper,
        'modal': Modal,
        'image-element': ImageElement,
        "form-field-template": FormFieldTemplate,
    }
};
</script>

<style scoped>

.img-click {
    width: 100px !important;
    height: 100px !important;
}
.profile-user-img:hover{
    border: 3px solid #3c8dbc;
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

#rotate{
    margin-top: 15px;
    margin-bottom: 5px;
}

#rotate ,#ratio{
    border-radius: 5px;
}

.rotate-button button.active {
    background-color: #007bff; /* Change to your desired active background color */
    color: #fff; /* Change to your desired active text color */
}
</style>

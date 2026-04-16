<template>

    <li :class="isMenuExtended ? 'menu-open nav-item' : 'nav-item'" :key="count">

        <a class="nav-link" :class="{'active-navigation-element active': isMainActive || isOneOfChildrenActive}"
           :href="getLink(menuItem)" @click.prevent="handleMainMenuAction">

		    <i :class="'nav-icon '+menuItem.iconClass"></i> &nbsp;

		    <router-link :to="menuItem.routeString" exact exact-active-class="active-navigation-element active"> {{ menuItem.name }} </router-link>

            <i v-if="isExpandable" class="nav-arrow fas fa-angle-left"></i>
		</a>

    	<ul class="nav nav-treeview" v-for="item in menuItem.children">

        	<li class="nav-item">

            	<router-link :to="item.routeString" v-tooltip="item.name" class="nav-link" exact exact-active-class="active-navigation-element active">

                    <i :class="'nav-icon '+item.iconClass"></i>

                    <p>{{ subString(item.name) }}</p>

                </router-link>
            </li>
        </ul>
    </li>
</template>

<script>

	import {getSubStringValue} from "../../helpers/extraLogics";

    export default {

		props : {

			menuItem : { type : Object }
		},

		data () {

			return {

     			isMenuExtended: false,

    			isExpandable: false,

    			isMainActive: false,

    			isOneOfChildrenActive: false,

                count: 0
			}
		},

		mounted () {

        	this.isExpandable =
            this.menuItem &&
            this.menuItem.children &&
            this.menuItem.children.length > 0;

            this.count = this.count + 1;

        	this.calculateIsActive(this.$route.path);

        	this.$router.afterEach((to) => {

            	this.calculateIsActive(to.path);
        	});
    	},


    	methods : {

            subString(value,length = 15){

                return getSubStringValue(value,length)
            },

    		handleMainMenuAction() {

		        if (this.isExpandable) {

		            this.toggleMenu();

                    this.count = this.count + 1;

		            return;
		        }

		        this.$router.replace(this.menuItem.routeString);
		    },

		    toggleMenu() {

		        this.isMenuExtended = !this.isMenuExtended;
		    },

            getLink(navigation){

                if(!Boolean(navigation.hasChildren)){

                    if(navigation.routeString === '/dashboard') {

                        return this.basePath() + navigation.routeString;

                    } else {

                        return navigation.redirectUrl.replace(this.basePath(), '');

                    }
                }
                return 'javascript:void(0);';
            },

		    calculateIsActive(url) {

        		this.isMainActive = false;

       	 		this.isOneOfChildrenActive = false;

        		if (this.isExpandable) {

            		this.menuItem.children.forEach((item) => {

                		if (item.routeString === url) {

                    		this.isOneOfChildrenActive = true;

                    		this.isMenuExtended = true;
               	 		}
            		});

        		} else if (this.menuItem.routeString === url) {

            		this.isMainActive = true;
        		}

        		if (!this.isMainActive && !this.isOneOfChildrenActive) {

            		this.isMenuExtended = false;
        		}
    		}
    	}
	}
</script>

<style scoped>

	.nav-item {
    	cursor: pointer;
	}

	.displayMenu { display : block !important; }

	.hideMenu { display : none !important; }
</style>

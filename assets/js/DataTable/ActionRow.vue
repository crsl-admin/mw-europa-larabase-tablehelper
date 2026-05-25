<template>
  <div class="d-flex flex-nowrap align-center">
    <v-menu v-if="header.group" :location="location">
      <template v-slot:activator="{ props }">
        <v-btn density="comfortable" icon="more_vert" size="small" v-bind="props" value="true" variant="text" @click.stop/>
      </template>
      <v-list>
        <template v-for="(action, i) in actions">
          <v-divider v-if="i > 0"/>
          <v-list-item v-bind="{
                        title: action.label ?? action.name,
                        baseColor: action.color,
                        prependIcon: action.icon ?? action.prependIcon,
                    }" @click.stop="onAction(action)"/>
        </template>
      </v-list>
    </v-menu>
    <template v-for="{ bind, tooltip, isIconButton, action } in actionBindings" v-else>
      <v-tooltip v-if="isIconButton && tooltip" v-bind="tooltip">
        <template v-slot:activator="{ props }">
          <v-btn v-bind="Object.assign(props, bind)" @click.stop="onAction(action)"/>
        </template>
      </v-tooltip>
      <v-btn v-else v-bind="bind" @click.stop="onAction(action)"/>
    </template>
  </div>
</template>

<script>
import {trans} from 'laravel-vue-i18n';
import {router} from '@inertiajs/vue3';

export default {
  props: {
    /// Value = header definition
    value: {type: Object, required: true,},
    header: {type: Object, required: true,},
  },
  computed: {
    actions() {
      return this.header?.actions.map(action => Object.assign({
        label: this.getActionLabel(action),
        icon: this.getActionIcon(action),
        color: this.getActionColor(action),
      }, action));
    },
    actionBindings() {
      return this.header?.actions.map(action => {

        const fallback = {
          label: this.getActionLabel(action),
          icon: this.getActionIcon(action),
          color: this.getActionColor(action),
        };

        const isIconButton = action.style === 'icon' || !action.label && fallback.icon;

        const bind = (isIconButton) ? {
          class: 'ma-1',
          icon: action.icon ?? fallback.icon,
          density: action.density ?? 'comfortable',
          variant: action.variant ?? 'text',
          color: fallback.color,
          text: fallback.label,
          size: action.size ?? 'small',
        } : {
          class: 'mr-1 mb-1',
          prependIcon: action.icon ?? fallback.icon,
          text: fallback.label,
          color: fallback.color,
          size: action.size ?? 'small',
          density: action.density,
          variant: action.variant ?? 'tonal',
        };

        const tooltip = fallback.label ? {
          text: action.label ?? action.name,
          //color: action.color,
        } : null;

        return {
          isIconButton,
          tooltip,
          action,
          bind
        };
      });
    },
    localizations() {
      return this.header?.actionsLocalization;
    },
  },
  methods: {
    getActionLabel(action) {
      if (action.label)
        return action.label;
      if (!this.localizations)
        return action.label;


      if (this.localizations[action.name])
        return this.localizations[action.name];


      const tr = trans(`${this.localizations}.${action.name}`);
      return tr;
      return (tr !== `${this.localizations}.${action.name}`) ? tr : null;
    },
    getActionColor(action) {
      if (action.color)
        return action.color;
      return {
        edit: 'primary',
        destroy: 'primary',
        show: 'primary',
      }[action.name] ?? 'primary';
    },
    getActionIcon(action) {
      if (action.icon)
        return action.icon;
      return {
        edit: 'edit',
        destroy: 'delete',
        show: 'visibility',
      }[action.name] ?? null;
    },
    onAction(action) {
      console.log('onAction', Object.assign({}, action));

      const url = this.getActionUrl(action);
      const method = action.method ?? 'get';

      if (!action.confirm || confirm(action.confirm)) {
        if (url) {
          if (method.toLowerCase() === 'get') {
            router.get(url);
          } else {
            router.visit(url, { method });
          }
        } else {
          // If no URL, emit event to parent
          this.$emit('action', { action: action.name, item: this.value });
        }
      }
    },
    getActionUrl(action) {
      //route
      if (action.route) {
        let data = Object.assign({}, action);

        if (action.routeBindings) {
          let binds = action.routeBindings;
          if (typeof binds === 'string') {
            binds = binds.split(',');
          }

          if (Array.isArray(binds)) {
            data = binds.map((k) => this.value[k]);
          } else {
            data = {};
            for (const key of binds) {
              const mapTo = binds[key];
              data[key] = this.data[mapTo];
            }
          }
        }

        return route(action.route, data);

      }
      return action.link;
    },

  }
}
</script>

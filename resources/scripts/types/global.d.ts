import { PageProps as InertiaPageProps } from '@inertiajs/core';
import { AxiosInstance } from 'axios';
import { route as ziggyRoute } from '../../../vendor/tightenco/ziggy';
import { PageProps as AppPageProps } from './index';

declare global {
    interface Window {
        axios: AxiosInstance;
    }

    // noinspection ES6ConvertVarToLetConst
    var route: typeof ziggyRoute;
}

declare module 'vue' {
    interface ComponentCustomProperties {
        route: typeof ziggyRoute;
    }
}

declare module '@inertiajs/core' {
    interface PageProps extends InertiaPageProps, AppPageProps {}
}

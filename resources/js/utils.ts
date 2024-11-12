import { usePage } from '@inertiajs/vue3';
const page = usePage<{ userAbilities: string[], auth: any, ziggy: any }>();

export function userCan(permission: string): boolean {
    return page.props.userAbilities.includes(permission);
}
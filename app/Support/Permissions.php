<?php

namespace App\Support;

final class Permissions
{
    public const ACCESS_MANAGEMENT = 'access management';

    public const VIEW_DASHBOARD = 'view dashboard';

    public const MANAGE_PROJECTS = 'manage projects';

    public const MANAGE_PACKAGES = 'manage packages';

    public const MANAGE_CUSTOMERS = 'manage customers';

    public const MANAGE_STAFF = 'manage staff';

    public const MANAGE_BOOKINGS = 'manage bookings';

    public const MANAGE_PAYMENTS = 'manage payments';

    public const MANAGE_INSTALLMENTS = 'manage installments';

    public const MANAGE_ALLOTMENTS = 'manage allotments';

    public const MANAGE_COMMISSIONS = 'manage commissions';

    public const MANAGE_WITHDRAWALS = 'manage withdrawals';

    public const MANAGE_NOTIFICATIONS = 'manage notifications';

    public const VIEW_ACTIVITY_LOG = 'view activity log';

    public const USE_CUSTOMER_PORTAL = 'use customer portal';

    public const CUSTOMER_BOOKINGS_CREATE = 'customer.bookings.create';

    public const CUSTOMER_BOOKINGS_STORE = 'customer.bookings.store';

    public const CUSTOMER_PAYMENTS = 'customer.payments';

    public const CUSTOMER_PAYMENTS_RECEIPT = 'customer.payments.receipt';

    public const CUSTOMER_PAYMENTS_STORE = 'customer.payments.store';

    public static function customer(): array
    {
        return [
            self::VIEW_DASHBOARD,
            self::USE_CUSTOMER_PORTAL,
            self::CUSTOMER_BOOKINGS_CREATE,
            self::CUSTOMER_BOOKINGS_STORE,
            self::CUSTOMER_PAYMENTS,
            self::CUSTOMER_PAYMENTS_RECEIPT,
            self::CUSTOMER_PAYMENTS_STORE,
        ];
    }

    public static function all(): array
    {
        return array_values(array_unique([
            self::ACCESS_MANAGEMENT, self::VIEW_DASHBOARD, self::MANAGE_PROJECTS,
            self::MANAGE_PACKAGES, self::MANAGE_CUSTOMERS, self::MANAGE_STAFF,
            self::MANAGE_BOOKINGS, self::MANAGE_PAYMENTS,
            self::MANAGE_INSTALLMENTS, self::MANAGE_ALLOTMENTS, self::MANAGE_COMMISSIONS,
            self::MANAGE_WITHDRAWALS, self::MANAGE_NOTIFICATIONS, self::VIEW_ACTIVITY_LOG, self::USE_CUSTOMER_PORTAL,
            ...self::customer(),
        ]));
    }
}

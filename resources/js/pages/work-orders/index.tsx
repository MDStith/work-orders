import { Head, Link, router, usePage } from '@inertiajs/react';

type WorkOrder = {
    id: number;
    title: string;
    description: string;
    status: 'open' | 'in_progress' | 'completed';
};

type PaginatedWorkOrders = {
    data: WorkOrder[];
    current_page: number;
    last_page: number;
    total: number;
    from: number | null;
    to: number | null;
    prev_page_url: string | null;
    next_page_url: string | null;
};

type Props = {
    workOrders: PaginatedWorkOrders;
    filters: {
        status: string;
        search: string;
    };
    canUpdateWorkOrders: boolean;
    canCreateWorkOrders: boolean;
};

const statusLabels = {
    open: 'Open',
    in_progress: 'In Progress',
    completed: 'Completed',
};

export default function WorkOrdersIndex({
    workOrders,
    filters,
    canUpdateWorkOrders,
    canCreateWorkOrders,
}: Props) {
    const { errors } = usePage().props;

    return (
        <>
            <Head title="Work Orders" />

            <main className="mx-auto w-full max-w-5xl p-4 sm:p-6 lg:p-8">
                <div className="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <h1 className="text-3xl font-bold">Work Orders</h1>

                    {canCreateWorkOrders && (
                        <Link
                            href="/work-orders/create"
                            className="inline-flex items-center justify-center rounded bg-blue-600 px-4 py-2 text-white hover:bg-blue-700"
                        >
                            Create work order
                        </Link>
                    )}
                </div>

                <form
                    key={`${filters.status}:${filters.search}`}
                    onSubmit={(event) => {
                        event.preventDefault();

                        const formData = new FormData(event.currentTarget);
                        const statusValue = formData.get('status');
                        const searchValue = formData.get('search');

                        const status =
                            typeof statusValue === 'string' ? statusValue : '';
                        const search =
                            typeof searchValue === 'string'
                                ? searchValue.trim()
                                : '';

                        const query: Record<string, string> = {};

                        if (status) {
                            query.status = status;
                        }

                        if (search !== '') {
                            query.search = search;
                        }

                        router.get('/work-orders', query);
                    }}
                    className="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end"
                >
                    <div className="min-w-0 flex-1">
                        <label
                            htmlFor="search"
                            className="mb-2 block text-sm font-medium"
                        >
                            Search orders
                        </label>

                        <input
                            id="search"
                            name="search"
                            type="search"
                            defaultValue={filters.search}
                            maxLength={100}
                            placeholder="Search title or description"
                            aria-invalid={Boolean(errors.search)}
                            aria-describedby={
                                errors.search ? 'search-error' : undefined
                            }
                            className="w-full rounded border bg-background p-2"
                        />

                        {errors.search && (
                            <p
                                id="search-error"
                                className="mt-2 text-sm text-red-600"
                            >
                                {errors.search}
                            </p>
                        )}
                    </div>

                    <div>
                        <label
                            htmlFor="status"
                            className="mb-2 block text-sm font-medium"
                        >
                            Status
                        </label>

                        <select
                            id="status"
                            name="status"
                            defaultValue={filters.status}
                            className="w-full rounded border bg-background p-2"
                        >
                            <option value="">All statuses</option>
                            <option value="open">Open</option>
                            <option value="in_progress">In progress</option>
                            <option value="completed">Completed</option>
                        </select>
                    </div>

                    <button
                        type="submit"
                        className="rounded bg-blue-600 px-4 py-2 text-white hover:bg-blue-700"
                    >
                        Apply filters
                    </button>

                    <Link
                        href="/work-orders"
                        className="rounded border px-4 py-2 text-center hover:bg-accent"
                    >
                        Clear
                    </Link>
                </form>

                {workOrders.data.length === 0 && (
                    <div className="rounded-lg border border-dashed p-8 text-center">
                        <h2 className="text-lg font-semibold">
                            No work orders found
                        </h2>
                        <p className="mt-2 text-sm text-muted-foreground">
                            {filters.status || filters.search
                                ? 'No work orders match your search and filters.'
                                : 'Work orders will appear here once they are created.'}
                        </p>
                    </div>
                )}

                {workOrders.data.map((order) => (
                    <article
                        key={order.id}
                        className="mb-4 rounded-lg border p-4"
                    >
                        <h2 className="text-xl font-semibold">{order.title}</h2>

                        <p className="mt-2">{order.description}</p>

                        <p className="mt-2 text-sm">
                            Status: {statusLabels[order.status]}
                        </p>

                        {canUpdateWorkOrders && (
                            <div className="mt-3">
                                <Link
                                    href={`/work-orders/${order.id}/edit`}
                                    className="mb-3 inline-block rounded border px-3 py-2 text-sm hover:bg-accent"
                                >
                                    Edit details
                                </Link>
                                <br />

                                <label
                                    htmlFor={`status-${order.id}`}
                                    className="mr-3"
                                >
                                    Update status
                                </label>

                                <select
                                    id={`status-${order.id}`}
                                    defaultValue={order.status}
                                    onChange={(event) => {
                                        router.patch(
                                            `/work-orders/${order.id}`,
                                            {
                                                status: event.target.value,
                                                filter_status: filters.status,
                                                filter_search: filters.search,
                                            },
                                        );
                                    }}
                                    className="rounded border p-2"
                                >
                                    <option value="open">Open</option>
                                    <option value="in_progress">
                                        In progress
                                    </option>
                                    <option value="completed">Completed</option>
                                </select>
                            </div>
                        )}
                    </article>
                ))}
                {workOrders.total > 0 && (
                    <nav
                        aria-label="Work order pagination"
                        className="mt-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
                    >
                        <p className="text-sm text-muted-foreground">
                            Showing {workOrders.from ?? 0}–{workOrders.to ?? 0}{' '}
                            of {workOrders.total} orders
                        </p>

                        <div className="flex flex-wrap items-center gap-3">
                            {workOrders.prev_page_url ? (
                                <Link
                                    href={workOrders.prev_page_url}
                                    className="rounded border px-3 py-2 text-sm hover:bg-accent"
                                >
                                    Previous
                                </Link>
                            ) : (
                                <span
                                    aria-disabled="true"
                                    className="rounded border px-3 py-2 text-sm text-muted-foreground opacity-50"
                                >
                                    Previous
                                </span>
                            )}

                            <span className="text-sm">
                                Page {workOrders.current_page} of{' '}
                                {workOrders.last_page}
                            </span>

                            {workOrders.next_page_url ? (
                                <Link
                                    href={workOrders.next_page_url}
                                    className="rounded border px-3 py-2 text-sm hover:bg-accent"
                                >
                                    Next
                                </Link>
                            ) : (
                                <span
                                    aria-disabled="true"
                                    className="rounded border px-3 py-2 text-sm text-muted-foreground opacity-50"
                                >
                                    Next
                                </span>
                            )}
                        </div>
                    </nav>
                )}
            </main>
        </>
    );
}

WorkOrdersIndex.layout = {
    breadcrumbs: [
        {
            title: 'Work Orders',
            href: '/work-orders',
        },
    ],
};

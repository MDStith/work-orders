import { Head, Link, router } from '@inertiajs/react';

type WorkOrder = {
    id: number;
    title: string;
    description: string;
    status: 'open' | 'in_progress' | 'completed';
};

type Props = {
    workOrders: WorkOrder[];
    filters: {
        status: string;
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

                <div className="mb-6">
                    <label htmlFor="status" className="mr-3">
                        Status
                    </label>

                    <select
                        id="status"
                        value={filters.status}
                        onChange={(event) => {
                            const status = event.target.value;

                            router.get(
                                '/work-orders',
                                status ? { status } : {},
                            );
                        }}
                        className="rounded border p-2"
                    >
                        <option value="">All statuses</option>
                        <option value="open">Open</option>
                        <option value="in_progress">In progress</option>
                        <option value="completed">Completed</option>
                    </select>
                </div>

                {workOrders.length === 0 && (
                    <div className="rounded-lg border border-dashed p-8 text-center">
                        <h2 className="text-lg font-semibold">
                            No work orders found
                        </h2>
                        <p className="mt-2 text-sm text-muted-foreground">
                            {filters.status
                                ? 'No work orders match the selected status.'
                                : 'Work orders will appear here once they are created.'}
                        </p>
                    </div>
                )}

                {workOrders.map((order) => (
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

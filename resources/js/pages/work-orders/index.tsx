import { Head, Link, router } from '@inertiajs/react';

type WorkOrder = {
    id: number;
    title: string;
    description: string;
    status: string;
};

type Props = {
    workOrders: WorkOrder[];
    filters: {
        status: string;
    };
    canUpdateWorkOrders: boolean;
    canCreateWorkOrders: boolean;
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

            <main className="mx-auto max-w-4xl p-8">
                <h1 className="mb-6 text-3xl font-bold">Work Orders</h1>

                {canCreateWorkOrders && (
                    <Link
                        href="/work-orders/create"
                        className="mb-6 inline-block rounded bg-blue-600 px-4 py-2 text-white"
                    >
                        Create work order
                    </Link>
                )}

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

                {workOrders.map((order) => (
                    <article
                        key={order.id}
                        className="mb-4 rounded-lg border p-4"
                    >
                        <h2 className="text-xl font-semibold">{order.title}</h2>

                        <p className="mt-2">{order.description}</p>

                        <p className="mt-2 text-sm">Status: {order.status}</p>

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

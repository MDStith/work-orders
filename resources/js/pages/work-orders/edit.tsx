import { Head, Link, useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';

type Props = {
    workOrder: {
        id: number;
        title: string;
        description: string;
    };
};

export default function EditWorkOrder({ workOrder }: Props) {
    const { data, setData, put, processing, errors } = useForm({
        title: workOrder.title,
        description: workOrder.description,
    });

    function submit(event: FormEvent<HTMLFormElement>) {
        event.preventDefault();

        put(`/work-orders/${workOrder.id}`);
    }

    return (
        <>
            <Head title="Edit Work Order" />

            <main className="mx-auto w-full max-w-5xl p-4 sm:p-6 lg:p-8">
                <h1 className="mb-6 text-3xl font-bold">Edit Work Order</h1>

                <form onSubmit={submit} className="space-y-6">
                    <div>
                        <label htmlFor="title" className="mb-2 block">
                            Title
                        </label>

                        <input
                            id="title"
                            type="text"
                            value={data.title}
                            onChange={(event) =>
                                setData('title', event.target.value)
                            }
                            required
                            maxLength={255}
                            aria-invalid={Boolean(errors.title)}
                            aria-describedby={
                                errors.title ? 'title-error' : undefined
                            }
                            className="w-full rounded border bg-background p-2"
                        />

                        {errors.title && (
                            <p
                                id="title-error"
                                className="mt-2 text-sm text-red-600"
                            >
                                {errors.title}
                            </p>
                        )}
                    </div>

                    <div>
                        <label htmlFor="description" className="mb-2 block">
                            Description
                        </label>

                        <textarea
                            id="description"
                            value={data.description}
                            onChange={(event) =>
                                setData('description', event.target.value)
                            }
                            required
                            maxLength={10000}
                            rows={6}
                            aria-invalid={Boolean(errors.description)}
                            aria-describedby={
                                errors.description
                                    ? 'description-error'
                                    : undefined
                            }
                            className="w-full rounded border bg-background p-2"
                        />

                        {errors.description && (
                            <p
                                id="description-error"
                                className="mt-2 text-sm text-red-600"
                            >
                                {errors.description}
                            </p>
                        )}
                    </div>

                    <p className="text-sm text-muted-foreground">
                        Change the status from the work-order listing.
                    </p>

                    <div className="flex flex-wrap items-center gap-4">
                        <button
                            type="submit"
                            disabled={processing}
                            className="rounded bg-blue-600 px-4 py-2 text-white hover:bg-blue-700 disabled:opacity-50"
                        >
                            {processing ? 'Saving...' : 'Save changes'}
                        </button>

                        <Link href="/work-orders" className="underline">
                            Cancel
                        </Link>
                    </div>
                </form>
            </main>
        </>
    );
}

EditWorkOrder.layout = {
    breadcrumbs: [
        {
            title: 'Work Orders',
            href: '/work-orders',
        },
        {
            title: 'Edit',
        },
    ],
};

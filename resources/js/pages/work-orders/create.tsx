import { Head, Link, useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';

export default function CreateWorkOrder() {
    const { data, setData, post, processing, errors } = useForm({
        title: '',
        description: '',
    });

    function submit(event: FormEvent<HTMLFormElement>) {
        event.preventDefault();

        post('/work-orders');
    }

    return (
        <>
            <Head title="Create Work Order" />

            <main className="mx-auto max-w-4xl p-8">
                <h1 className="mb-6 text-3xl font-bold">Create Work Order</h1>

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
                            className="w-full rounded border p-2"
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
                            className="w-full rounded border p-2"
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

                    <p className="text-sm">
                        New work orders start with an Open status.
                    </p>

                    <div className="flex items-center gap-4">
                        <button
                            type="submit"
                            disabled={processing}
                            className="rounded bg-blue-600 px-4 py-2 text-white disabled:opacity-50"
                        >
                            {processing ? 'Creating...' : 'Create work order'}
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

CreateWorkOrder.layout = {
    breadcrumbs: [
        {
            title: 'Work Orders',
            href: '/work-orders',
        },
        {
            title: 'Create',
            href: '/work-orders/create',
        },
    ],
};

import { Head, Link, usePage } from '@inertiajs/react';
import { dashboard, login, register } from '@/routes';

export default function Welcome() {
    const { auth } = usePage().props;

    return (
        <>
            <Head title="Welcome" />

            <div className="min-h-screen bg-background text-foreground">
                <header className="mx-auto flex w-full max-w-5xl flex-wrap items-center justify-between gap-4 px-6 py-6">
                    <span className="text-lg font-semibold">Work Orders</span>

                    <nav
                        aria-label="Account navigation"
                        className="flex items-center gap-3"
                    >
                        {auth.user ? (
                            <Link
                                href={dashboard()}
                                className="rounded border px-4 py-2 text-sm hover:bg-accent"
                            >
                                Dashboard
                            </Link>
                        ) : (
                            <>
                                <Link
                                    href={login()}
                                    className="rounded border px-4 py-2 text-sm hover:bg-accent"
                                >
                                    Log in
                                </Link>

                                <Link
                                    href={register()}
                                    className="rounded bg-blue-600 px-4 py-2 text-sm text-white hover:bg-blue-700"
                                >
                                    Register
                                </Link>
                            </>
                        )}
                    </nav>
                </header>

                <main className="mx-auto w-full max-w-5xl px-6 py-12 sm:py-20">
                    <section aria-labelledby="welcome-heading">
                        <p className="mb-4 text-sm font-medium text-muted-foreground">
                            Work-order management
                        </p>

                        <h1
                            id="welcome-heading"
                            className="max-w-3xl text-4xl font-bold tracking-tight sm:text-5xl"
                        >
                            Keep track of the work that needs doing.
                        </h1>

                        <p className="mt-6 max-w-2xl text-lg text-muted-foreground">
                            Find work orders, review their details, and follow
                            progress from Open to Completed.
                        </p>

                        <div className="mt-8">
                            {auth.user ? (
                                <Link
                                    href="/work-orders"
                                    className="inline-block rounded bg-blue-600 px-5 py-3 font-medium text-white hover:bg-blue-700"
                                >
                                    View work orders
                                </Link>
                            ) : (
                                <Link
                                    href={login()}
                                    className="inline-block rounded bg-blue-600 px-5 py-3 font-medium text-white hover:bg-blue-700"
                                >
                                    Log in to view orders
                                </Link>
                            )}
                        </div>
                    </section>

                    <section
                        aria-label="Application features"
                        className="mt-16 grid gap-6 sm:grid-cols-3"
                    >
                        <article className="rounded-lg border p-6">
                            <h2 className="text-lg font-semibold">
                                Find an order
                            </h2>
                            <p className="mt-3 text-sm text-muted-foreground">
                                Search titles and descriptions, filter by
                                status, and browse results page by page.
                            </p>
                        </article>

                        <article className="rounded-lg border p-6">
                            <h2 className="text-lg font-semibold">
                                Follow progress
                            </h2>
                            <p className="mt-3 text-sm text-muted-foreground">
                                Review Open, In Progress, and Completed orders,
                                with dashboard totals for each status.
                            </p>
                        </article>

                        <article className="rounded-lg border p-6">
                            <h2 className="text-lg font-semibold">
                                Manage the details
                            </h2>
                            <p className="mt-3 text-sm text-muted-foreground">
                                Dispatchers can create orders, edit details,
                                update statuses, and delete orders. New accounts
                                receive Viewer access.
                            </p>
                        </article>
                    </section>
                </main>
            </div>
        </>
    );
}

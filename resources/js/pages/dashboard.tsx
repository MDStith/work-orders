import { Head, Link } from '@inertiajs/react';
import { dashboard } from '@/routes';

type Props = {
    stats: {
        total: number;
        open: number;
        in_progress: number;
        completed: number;
    };
};

export default function Dashboard({ stats }: Props) {
    const cards = [
        {
            title: 'Total orders',
            count: stats.total,
            href: '/work-orders',
        },
        {
            title: 'Open',
            count: stats.open,
            href: '/work-orders?status=open',
        },
        {
            title: 'In progress',
            count: stats.in_progress,
            href: '/work-orders?status=in_progress',
        },
        {
            title: 'Completed',
            count: stats.completed,
            href: '/work-orders?status=completed',
        },
    ];

    return (
        <>
            <Head title="Dashboard" />

            <main className="mx-auto w-full max-w-5xl p-4 sm:p-6 lg:p-8">
                <h1 className="text-3xl font-bold">Dashboard</h1>

                <p className="mt-2 text-muted-foreground">
                    View work-order totals and select a status to explore its
                    orders.
                </p>

                <div className="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    {cards.map((card) => (
                        <Link
                            key={card.title}
                            href={card.href}
                            className="rounded-lg border bg-card p-6 text-card-foreground transition-colors hover:bg-accent focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-ring"
                        >
                            <h2 className="text-sm font-medium">
                                {card.title}
                            </h2>

                            <p className="mt-2 text-3xl font-bold">
                                {card.count}
                            </p>
                        </Link>
                    ))}
                </div>
            </main>
        </>
    );
}

Dashboard.layout = {
    breadcrumbs: [
        {
            title: 'Dashboard',
            href: dashboard(),
        },
    ],
};

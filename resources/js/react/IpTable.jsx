const themes = {
    tailwind: {
        caption: 'sr-only',
        card: 'overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-900',
        header: 'border-b border-gray-200 px-4 py-3 dark:border-gray-700',
        title: 'text-sm font-semibold text-gray-900 dark:text-gray-100',
        table: 'min-w-full divide-y divide-gray-200 text-left text-sm dark:divide-gray-700',
        th: 'px-4 py-2 text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400',
        label: 'whitespace-nowrap px-4 py-2 font-normal text-gray-600 dark:text-gray-300',
        cell: 'px-4 py-2',
        code: 'rounded bg-gray-100 px-1.5 py-0.5 font-mono text-xs text-gray-900 dark:bg-gray-800 dark:text-gray-100',
        empty: 'text-gray-400 dark:text-gray-500',
    },
    bootstrap5: {
        caption: 'visually-hidden',
        card: 'card',
        header: 'card-header',
        title: 'h6 mb-0',
        table: 'table table-sm mb-0 align-middle',
        th: 'text-secondary text-uppercase small',
        label: 'fw-normal text-body-secondary',
        cell: '',
        code: 'font-monospace',
        empty: 'text-secondary',
    },
    bootstrap4: {
        caption: 'sr-only',
        card: 'card',
        header: 'card-header',
        title: 'h6 mb-0',
        table: 'table table-sm mb-0',
        th: 'text-muted text-uppercase small',
        label: 'font-weight-normal text-muted',
        cell: '',
        code: '',
        empty: 'text-muted',
    },
}

export default function IpTable({
    rows = [],
    title = 'Captured IP addresses',
    theme = 'tailwind',
    eventHeader = 'Event',
    valueHeader = 'Address',
    emptyText = 'No IP addresses have been captured yet.',
}) {
    const css = themes[theme] ?? themes.tailwind

    return (
        <div className={css.card}>
            <div className={css.header}>
                <h3 className={css.title}>{title}</h3>
            </div>

            <div className="table-responsive">
                <table className={css.table}>
                    <caption className={css.caption}>{title}</caption>
                    <thead>
                        <tr>
                            <th scope="col" className={css.th}>{eventHeader}</th>
                            <th scope="col" className={css.th}>{valueHeader}</th>
                        </tr>
                    </thead>
                    <tbody>
                        {rows.map((row) => (
                            <tr key={row.column} data-column={row.column}>
                                <th scope="row" className={css.label}>{row.label}</th>
                                <td className={css.cell}>
                                    {row.captured
                                        ? <code className={css.code}>{row.value}</code>
                                        : <span className={css.empty}>{row.value}</span>}
                                </td>
                            </tr>
                        ))}
                        {rows.length === 0 && (
                            <tr>
                                <td colSpan="2" className={css.empty}>{emptyText}</td>
                            </tr>
                        )}
                    </tbody>
                </table>
            </div>
        </div>
    )
}

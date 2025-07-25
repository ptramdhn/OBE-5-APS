import {
    Breadcrumb,
    BreadcrumbItem,
    BreadcrumbLink,
    BreadcrumbList,
    BreadcrumbPage,
    BreadcrumbSeparator,
} from '@/Components/ui/breadcrumb';
import { Fragment } from 'react';

export default function BreadcrumbHeader({ items }) {
    return (
        <Breadcrumb>
            <BreadcrumbList>
                {items.map((item, index) => (
                    <Fragment key={index}>
                        <BreadcrumbItem>
                            {item.href ? (
                                <BreadcrumbLink
                                    href={item.href}
                                    className="transition-colors hover:text-blue-600 dark:hover:text-blue-400"
                                >
                                    {item.label}
                                </BreadcrumbLink>
                            ) : (
                                <BreadcrumbPage className="font-medium text-blue-700 dark:text-blue-300">
                                    {item.label}
                                </BreadcrumbPage>
                            )}
                        </BreadcrumbItem>
                        {index < items.length - 1 && <BreadcrumbSeparator />}
                    </Fragment>
                ))}
            </BreadcrumbList>
        </Breadcrumb>
    );
}

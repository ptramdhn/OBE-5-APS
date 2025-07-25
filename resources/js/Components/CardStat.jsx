import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { cn } from '@/lib/utils';

export default function CardStat({ data, children }) {
    const { title, background, className = '', icon: Icon, iconClassName = '' } = data;

    return (
        <Card className={cn(background, className)}>
            <CardHeader className="flex flex-row items-start justify-between space-y-0 pb-2">
                <CardTitle className="text-ms text-wrap font-medium text-white"> {title}</CardTitle>
                {Icon && <Icon className={cn('size-8 text-white', iconClassName)} />}{' '}
            </CardHeader>
            <CardContent>{children}</CardContent>
        </Card>
    );
}

import { useInventoryQueries } from "@/hooks/use-inventory";
import { format } from "date-fns";
import { Card } from "@/components/ui/card";
import { Skeleton } from "@/components/ui/skeleton";
import { Clock, ArrowRight } from "lucide-react";

export default function History() {
  const { useHistory } = useInventoryQueries();
  const { data: history, isLoading } = useHistory();

  const getActionBadge = (action: string) => {
    const base = "inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold uppercase tracking-wider";
    switch (action) {
      case "created":
        return <span className={`${base} bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400`}>Created</span>;
      case "updated":
        return <span className={`${base} bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400`}>Updated</span>;
      case "deleted":
        return <span className={`${base} bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400`}>Deleted</span>;
      case "stock_added":
        return <span className={`${base} bg-teal-100 text-teal-800 dark:bg-teal-900/30 dark:text-teal-400`}>Stock Added</span>;
      case "stock_removed":
        return <span className={`${base} bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-400`}>Stock Removed</span>;
      default:
        return <span className={`${base} bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300`}>{action}</span>;
    }
  };

  return (
    <div className="space-y-6 animate-in fade-in slide-in-from-bottom-4 duration-500 h-full flex flex-col">
      <div>
        <h1 className="text-3xl font-display font-bold text-foreground">History Log</h1>
        <p className="text-muted-foreground mt-1">Audit trail of all stock and item changes.</p>
      </div>

      <Card className="border-border/50 shadow-sm flex-1 flex flex-col overflow-hidden">
        <div className="flex-1 overflow-auto">
          <table className="w-full text-sm text-left">
            <thead className="text-xs text-muted-foreground uppercase bg-muted/30 sticky top-0 backdrop-blur-md z-10">
              <tr>
                <th className="px-6 py-4 font-medium w-48">Date & Time</th>
                <th className="px-6 py-4 font-medium">Item Name</th>
                <th className="px-6 py-4 font-medium w-40">Action</th>
                <th className="px-6 py-4 font-medium text-center w-40">Quantity Change</th>
                <th className="px-6 py-4 font-medium">Note</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-border/50">
              {isLoading ? (
                Array.from({ length: 8 }).map((_, i) => (
                  <tr key={i}>
                    <td className="px-6 py-4"><Skeleton className="h-5 w-32" /></td>
                    <td className="px-6 py-4"><Skeleton className="h-5 w-40" /></td>
                    <td className="px-6 py-4"><Skeleton className="h-6 w-24 rounded-full" /></td>
                    <td className="px-6 py-4"><Skeleton className="h-5 w-20 mx-auto" /></td>
                    <td className="px-6 py-4"><Skeleton className="h-5 w-48" /></td>
                  </tr>
                ))
              ) : history && history.length > 0 ? (
                history.map((event) => (
                  <tr key={event.id} className="bg-background hover:bg-muted/30 transition-colors">
                    <td className="px-6 py-4 whitespace-nowrap text-muted-foreground">
                      <div className="flex items-center gap-2">
                        <Clock className="w-4 h-4 text-muted-foreground/60" />
                        {format(new Date(event.createdAt), "MMM d, yyyy HH:mm")}
                      </div>
                    </td>
                    <td className="px-6 py-4 font-medium text-foreground">
                      {event.itemName}
                    </td>
                    <td className="px-6 py-4">
                      {getActionBadge(event.action)}
                    </td>
                    <td className="px-6 py-4">
                      {(event.quantityBefore != null && event.quantityAfter != null) ? (
                        <div className="flex items-center justify-center gap-2 font-medium">
                          <span className="text-muted-foreground">{event.quantityBefore}</span>
                          <ArrowRight className="w-3 h-3 text-muted-foreground/50" />
                          <span className={
                            event.quantityAfter > event.quantityBefore 
                              ? "text-teal-600 dark:text-teal-400" 
                              : event.quantityAfter < event.quantityBefore 
                                ? "text-orange-600 dark:text-orange-400" 
                                : "text-foreground"
                          }>
                            {event.quantityAfter}
                          </span>
                        </div>
                      ) : (
                        <span className="text-muted-foreground text-center block">-</span>
                      )}
                    </td>
                    <td className="px-6 py-4 text-muted-foreground italic text-sm">
                      {event.note || "-"}
                    </td>
                  </tr>
                ))
              ) : (
                <tr>
                  <td colSpan={5}>
                    <div className="flex flex-col items-center justify-center py-16 text-muted-foreground">
                      <History className="w-12 h-12 mb-4 text-muted-foreground/50" />
                      <p className="text-lg font-medium text-foreground">No history events</p>
                      <p className="text-sm">Changes to your inventory will appear here.</p>
                    </div>
                  </td>
                </tr>
              )}
            </tbody>
          </table>
        </div>
      </Card>
    </div>
  );
}

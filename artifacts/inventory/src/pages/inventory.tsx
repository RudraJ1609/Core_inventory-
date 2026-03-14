import { useState } from "react";
import { useInventoryQueries } from "@/hooks/use-inventory";
import { useDebounce } from "@/hooks/use-debounce";
import { useAuth } from "@/hooks/use-auth";
import { InventoryItem } from "@workspace/api-client-react";
import { ItemDialog } from "@/components/item-dialog";
import { DeleteDialog } from "@/components/delete-dialog";
import { formatCurrency } from "@/lib/utils";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Card } from "@/components/ui/card";
import { Skeleton } from "@/components/ui/skeleton";
import { Plus, Search, Edit2, Trash2, PackageX, Eye } from "lucide-react";

export default function Inventory() {
  const [searchTerm, setSearchTerm] = useState("");
  const debouncedSearch = useDebounce(searchTerm, 300);
  const { isManager } = useAuth();

  const { useItems } = useInventoryQueries();
  const { data: items, isLoading } = useItems({ search: debouncedSearch || undefined });

  const [isItemDialogOpen, setIsItemDialogOpen] = useState(false);
  const [isDeleteDialogOpen, setIsDeleteDialogOpen] = useState(false);
  const [selectedItem, setSelectedItem] = useState<InventoryItem | null>(null);

  const handleAddClick = () => {
    setSelectedItem(null);
    setIsItemDialogOpen(true);
  };

  const handleEditClick = (item: InventoryItem) => {
    setSelectedItem(item);
    setIsItemDialogOpen(true);
  };

  const handleDeleteClick = (item: InventoryItem) => {
    setSelectedItem(item);
    setIsDeleteDialogOpen(true);
  };

  return (
    <div className="space-y-6 animate-in fade-in slide-in-from-bottom-4 duration-500 h-full flex flex-col">
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 className="text-3xl font-display font-bold text-foreground">Inventory</h1>
          <p className="text-muted-foreground mt-1">
            {isManager
              ? "Manage your items, prices, and stock levels."
              : "View-only access. Contact a manager to make changes."}
          </p>
        </div>
        {isManager && (
          <Button onClick={handleAddClick} className="shadow-md hover:shadow-lg transition-all rounded-xl">
            <Plus className="w-4 h-4 mr-2" />
            Add Item
          </Button>
        )}
        {!isManager && (
          <div className="flex items-center gap-1.5 text-xs text-muted-foreground bg-muted/50 border border-border/50 rounded-xl px-3 py-2">
            <Eye className="w-3.5 h-3.5" />
            View only
          </div>
        )}
      </div>

      <Card className="border-border/50 shadow-sm flex-1 flex flex-col overflow-hidden">
        <div className="p-4 border-b border-border/50 bg-muted/20">
          <div className="relative max-w-md">
            <Search className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground" />
            <Input
              placeholder="Search by name or category..."
              className="pl-9 bg-background rounded-xl border-border/60 focus-visible:ring-primary/20"
              value={searchTerm}
              onChange={(e) => setSearchTerm(e.target.value)}
            />
          </div>
        </div>

        <div className="flex-1 overflow-auto">
          <table className="w-full text-sm text-left">
            <thead className="text-xs text-muted-foreground uppercase bg-muted/30 sticky top-0 backdrop-blur-md z-10">
              <tr>
                <th className="px-6 py-4 font-medium">Item Name</th>
                <th className="px-6 py-4 font-medium">Category</th>
                <th className="px-6 py-4 font-medium text-right">Quantity</th>
                <th className="px-6 py-4 font-medium text-right">Price</th>
                <th className="px-6 py-4 font-medium text-right">Total Value</th>
                {isManager && <th className="px-6 py-4 font-medium text-center">Actions</th>}
              </tr>
            </thead>
            <tbody className="divide-y divide-border/50">
              {isLoading ? (
                Array.from({ length: 5 }).map((_, i) => (
                  <tr key={i}>
                    <td className="px-6 py-4"><Skeleton className="h-5 w-32" /></td>
                    <td className="px-6 py-4"><Skeleton className="h-5 w-24" /></td>
                    <td className="px-6 py-4"><Skeleton className="h-5 w-12 ml-auto" /></td>
                    <td className="px-6 py-4"><Skeleton className="h-5 w-16 ml-auto" /></td>
                    <td className="px-6 py-4"><Skeleton className="h-5 w-20 ml-auto" /></td>
                    {isManager && <td className="px-6 py-4"><Skeleton className="h-8 w-16 mx-auto" /></td>}
                  </tr>
                ))
              ) : items && items.length > 0 ? (
                items.map((item) => (
                  <tr key={item.id} className="bg-background hover:bg-muted/30 transition-colors group">
                    <td className="px-6 py-4 font-medium text-foreground">
                      {item.name}
                    </td>
                    <td className="px-6 py-4 text-muted-foreground">
                      <span className="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-secondary text-secondary-foreground">
                        {item.category}
                      </span>
                    </td>
                    <td className="px-6 py-4 text-right">
                      <span className={`font-semibold ${item.quantity <= 5 ? 'text-red-600 dark:text-red-400' : 'text-foreground'}`}>
                        {item.quantity}
                        {item.quantity <= 5 && (
                          <span className="ml-1.5 text-xs font-normal text-red-500">low</span>
                        )}
                      </span>
                    </td>
                    <td className="px-6 py-4 text-right text-muted-foreground">
                      {formatCurrency(item.price)}
                    </td>
                    <td className="px-6 py-4 text-right font-medium text-foreground">
                      {formatCurrency(item.quantity * item.price)}
                    </td>
                    {isManager && (
                      <td className="px-6 py-4">
                        <div className="flex items-center justify-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                          <Button
                            variant="ghost"
                            size="icon"
                            className="h-8 w-8 text-muted-foreground hover:text-primary hover:bg-primary/10"
                            onClick={() => handleEditClick(item)}
                          >
                            <Edit2 className="w-4 h-4" />
                          </Button>
                          <Button
                            variant="ghost"
                            size="icon"
                            className="h-8 w-8 text-muted-foreground hover:text-destructive hover:bg-destructive/10"
                            onClick={() => handleDeleteClick(item)}
                          >
                            <Trash2 className="w-4 h-4" />
                          </Button>
                        </div>
                      </td>
                    )}
                  </tr>
                ))
              ) : (
                <tr>
                  <td colSpan={isManager ? 6 : 5}>
                    <div className="flex flex-col items-center justify-center py-16 text-muted-foreground">
                      <PackageX className="w-12 h-12 mb-4 text-muted-foreground/50" />
                      <p className="text-lg font-medium text-foreground">No items found</p>
                      <p className="text-sm">Try adjusting your search or add a new item.</p>
                      {debouncedSearch && (
                        <Button variant="link" onClick={() => setSearchTerm("")} className="mt-2">
                          Clear search
                        </Button>
                      )}
                    </div>
                  </td>
                </tr>
              )}
            </tbody>
          </table>
        </div>
      </Card>

      {isManager && (
        <>
          <ItemDialog
            item={selectedItem}
            isOpen={isItemDialogOpen}
            onOpenChange={setIsItemDialogOpen}
          />
          <DeleteDialog
            item={selectedItem}
            isOpen={isDeleteDialogOpen}
            onOpenChange={setIsDeleteDialogOpen}
          />
        </>
      )}
    </div>
  );
}

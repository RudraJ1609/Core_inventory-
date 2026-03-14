import {
  AlertDialog,
  AlertDialogAction,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
} from "@/components/ui/alert-dialog";
import { useInventoryMutations } from "@/hooks/use-inventory";
import { InventoryItem } from "@workspace/api-client-react";
import { useToast } from "@/hooks/use-toast";

interface DeleteDialogProps {
  item?: InventoryItem | null;
  isOpen: boolean;
  onOpenChange: (open: boolean) => void;
}

export function DeleteDialog({ item, isOpen, onOpenChange }: DeleteDialogProps) {
  const { deleteItem } = useInventoryMutations();
  const { toast } = useToast();

  const handleDelete = async () => {
    if (!item) return;
    try {
      await deleteItem.mutateAsync({ id: item.id });
      toast({ title: "Item deleted successfully" });
      onOpenChange(false);
    } catch (error) {
      toast({ 
        title: "Error deleting item", 
        variant: "destructive"
      });
    }
  };

  return (
    <AlertDialog open={isOpen} onOpenChange={onOpenChange}>
      <AlertDialogContent>
        <AlertDialogHeader>
          <AlertDialogTitle>Are you absolutely sure?</AlertDialogTitle>
          <AlertDialogDescription>
            This will permanently delete <strong>{item?.name}</strong> from your inventory.
            This action cannot be undone.
          </AlertDialogDescription>
        </AlertDialogHeader>
        <AlertDialogFooter>
          <AlertDialogCancel>Cancel</AlertDialogCancel>
          <AlertDialogAction 
            onClick={handleDelete}
            className="bg-destructive text-destructive-foreground hover:bg-destructive/90"
            disabled={deleteItem.isPending}
          >
            {deleteItem.isPending ? "Deleting..." : "Delete Item"}
          </AlertDialogAction>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>
  );
}

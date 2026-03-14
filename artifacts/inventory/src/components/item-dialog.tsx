import { useEffect } from "react";
import { useForm } from "react-hook-form";
import { zodResolver } from "@hookform/resolvers/zod";
import { z } from "zod";
import {
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
  DialogDescription,
  DialogFooter,
} from "@/components/ui/dialog";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { useInventoryMutations } from "@/hooks/use-inventory";
import { InventoryItem } from "@workspace/api-client-react";
import { useToast } from "@/hooks/use-toast";

const itemSchema = z.object({
  name: z.string().min(1, "Name is required"),
  category: z.string().min(1, "Category is required"),
  quantity: z.coerce.number().min(0, "Quantity cannot be negative"),
  price: z.coerce.number().min(0, "Price cannot be negative"),
});

type ItemFormData = z.infer<typeof itemSchema>;

interface ItemDialogProps {
  item?: InventoryItem | null;
  isOpen: boolean;
  onOpenChange: (open: boolean) => void;
}

export function ItemDialog({ item, isOpen, onOpenChange }: ItemDialogProps) {
  const { createItem, updateItem } = useInventoryMutations();
  const { toast } = useToast();
  const isEditing = !!item;

  const {
    register,
    handleSubmit,
    reset,
    formState: { errors, isSubmitting },
  } = useForm<ItemFormData>({
    resolver: zodResolver(itemSchema),
    defaultValues: {
      name: "",
      category: "",
      quantity: 0,
      price: 0,
    },
  });

  useEffect(() => {
    if (isOpen) {
      if (item) {
        reset({
          name: item.name,
          category: item.category,
          quantity: item.quantity,
          price: item.price,
        });
      } else {
        reset({
          name: "",
          category: "",
          quantity: 0,
          price: 0,
        });
      }
    }
  }, [isOpen, item, reset]);

  const onSubmit = async (data: ItemFormData) => {
    try {
      if (isEditing && item) {
        await updateItem.mutateAsync({ id: item.id, data });
        toast({ title: "Item updated successfully" });
      } else {
        await createItem.mutateAsync({ data });
        toast({ title: "Item created successfully" });
      }
      onOpenChange(false);
    } catch (error) {
      toast({ 
        title: "Error saving item", 
        description: error instanceof Error ? error.message : "Unknown error",
        variant: "destructive"
      });
    }
  };

  return (
    <Dialog open={isOpen} onOpenChange={onOpenChange}>
      <DialogContent className="sm:max-w-[425px]">
        <DialogHeader>
          <DialogTitle className="font-display text-xl">
            {isEditing ? "Edit Inventory Item" : "Add New Item"}
          </DialogTitle>
          <DialogDescription>
            {isEditing 
              ? "Update the details of the selected inventory item." 
              : "Fill in the details below to add a new item to your inventory."}
          </DialogDescription>
        </DialogHeader>
        
        <form onSubmit={handleSubmit(onSubmit)} className="space-y-4 py-4">
          <div className="space-y-2">
            <Label htmlFor="name">Item Name</Label>
            <Input id="name" placeholder="e.g. Wireless Mouse" {...register("name")} />
            {errors.name && <p className="text-sm text-destructive">{errors.name.message}</p>}
          </div>
          
          <div className="space-y-2">
            <Label htmlFor="category">Category</Label>
            <Input id="category" placeholder="e.g. Electronics" {...register("category")} />
            {errors.category && <p className="text-sm text-destructive">{errors.category.message}</p>}
          </div>
          
          <div className="grid grid-cols-2 gap-4">
            <div className="space-y-2">
              <Label htmlFor="quantity">Quantity</Label>
              <Input id="quantity" type="number" min="0" {...register("quantity")} />
              {errors.quantity && <p className="text-sm text-destructive">{errors.quantity.message}</p>}
            </div>
            
            <div className="space-y-2">
              <Label htmlFor="price">Price ($)</Label>
              <Input id="price" type="number" step="0.01" min="0" {...register("price")} />
              {errors.price && <p className="text-sm text-destructive">{errors.price.message}</p>}
            </div>
          </div>
          
          <DialogFooter className="pt-4">
            <Button type="button" variant="outline" onClick={() => onOpenChange(false)}>
              Cancel
            </Button>
            <Button type="submit" disabled={isSubmitting}>
              {isSubmitting ? "Saving..." : isEditing ? "Save Changes" : "Create Item"}
            </Button>
          </DialogFooter>
        </form>
      </DialogContent>
    </Dialog>
  );
}
